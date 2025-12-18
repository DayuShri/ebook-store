<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\VoucherUsage;
use App\Models\Book;
use App\Services\CatalogClient;
use App\Services\PaymentClient;
use App\Services\VoucherCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function preview(
        Request $request,
        VoucherCalculator $calc,
        CatalogClient $catalog
    ) {
        $data = $request->validate([
            'user_id' => ['required', 'uuid'],
            'selected_book_ids' => ['required', 'array', 'min:1'],
            'selected_book_ids.*' => ['uuid'],
            'voucher_code' => ['nullable', 'string'],
        ]);

        $cartItems = Cart::where('user_id', $data['user_id'])
            ->whereIn('book_id', $data['selected_book_ids'])
            ->get();

        if ($cartItems->isEmpty()) {
            return ApiResponse::error(
                'No selected cart items found',
                null,
                422
            );
        }

        // === ambil data buku ===
        $bookIds = $cartItems->pluck('book_id')->all();
        $booksMap = collect();

        if (env('CATALOG_DUMMY', true)) {
            $booksMap = Book::whereIn('id', $bookIds)->get()->keyBy('id');
        } else {
            $items = $catalog->bulk($bookIds);
            $booksMap = collect($items)->keyBy('id');
        }

        $lines = [];
        $subtotal = 0;

        foreach ($cartItems as $ci) {
            $book = $booksMap->get($ci->book_id);

            if (!$book) {
                return ApiResponse::error(
                    'Book not found',
                    ['book_id' => $ci->book_id],
                    422
                );
            }

            $price = (float) ($book['price'] ?? $book->price);
            $discPct = (float) ($book['discount_percentage'] ?? ($book->discount_percentage ?? 0));
            $finalPrice = $price - ($price * $discPct / 100);

            $lineTotal = $finalPrice * $ci->quantity;
            $subtotal += $lineTotal;

            $lines[] = [
                'book_id' => $ci->book_id,
                'quantity' => (int) $ci->quantity,
                'unit_price' => round($finalPrice, 2),
                'line_total' => round($lineTotal, 2),
            ];
        }

        $discount = 0;
        $voucherId = null;

        if (!empty($data['voucher_code'])) {
            $res = $calc->validateAndCalculate($data['voucher_code'], $subtotal);

            if (!$res['valid']) {
                return ApiResponse::error(
                    'Invalid voucher',
                    ['reason' => $res['reason']],
                    422
                );
            }

            $discount = $res['discount_amount'];
            $voucherId = $res['voucher']->id;
        }

        return ApiResponse::success(
            'Checkout preview generated',
            [
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($discount, 2),
                'final_amount' => round(max(0, $subtotal - $discount), 2),
                'voucher_id' => $voucherId,
                'items' => $lines,
            ]
        );
    }

    public function placeOrder(
        Request $request,
        VoucherCalculator $calc,
        CatalogClient $catalog,
        PaymentClient $payment
    ) {
        $data = $request->validate([
            'user_id' => ['required', 'uuid'],
            'selected_book_ids' => ['required', 'array', 'min:1'],
            'selected_book_ids.*' => ['uuid'],
            'voucher_code' => ['nullable', 'string'],
            'payment_method' => ['required', 'string', 'max:50'],
        ]);

        return DB::transaction(function () use ($data, $calc, $catalog, $payment) {

            $cartItems = Cart::where('user_id', $data['user_id'])
                ->whereIn('book_id', $data['selected_book_ids'])
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                return ApiResponse::error(
                    'No selected cart items found',
                    null,
                    422
                );
            }

            $bookIds = $cartItems->pluck('book_id')->all();
            $booksMap = env('CATALOG_DUMMY', true)
                ? Book::whereIn('id', $bookIds)->get()->keyBy('id')
                : collect($catalog->bulk($bookIds))->keyBy('id');

            $subtotal = 0;
            $itemsPayload = [];

            foreach ($cartItems as $ci) {
                $book = $booksMap->get($ci->book_id);

                if (!$book) {
                    return ApiResponse::error(
                        'Book not found',
                        ['book_id' => $ci->book_id],
                        422
                    );
                }

                $price = (float) ($book['price'] ?? $book->price);
                $discPct = (float) ($book['discount_percentage'] ?? ($book->discount_percentage ?? 0));
                $finalPrice = $price - ($price * $discPct / 100);

                $lineSubtotal = $finalPrice * $ci->quantity;
                $subtotal += $lineSubtotal;

                $itemsPayload[] = [
                    'book_id' => $ci->book_id,
                    'quantity' => $ci->quantity,
                    'unit_price' => round($finalPrice, 2),
                    'subtotal' => round($lineSubtotal, 2),
                ];
            }

            $discount = 0;
            $voucherId = null;

            if (!empty($data['voucher_code'])) {
                $res = $calc->validateAndCalculate($data['voucher_code'], $subtotal);

                if (!$res['valid']) {
                    return ApiResponse::error(
                        'Invalid voucher',
                        ['reason' => $res['reason']],
                        422
                    );
                }

                $discount = $res['discount_amount'];
                $voucherId = $res['voucher']->id;
            }

            $order = Order::create([
                'id' => Str::uuid(),
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'user_id' => $data['user_id'],
                'total_amount' => $subtotal,
                'discount_amount' => $discount,
                'final_amount' => max(0, $subtotal - $discount),
                'voucher_id' => $voucherId,
                'status' => 'pending',
            ]);

            foreach ($itemsPayload as $item) {
                OrderItem::create([
                    'id' => Str::uuid(),
                    'order_id' => $order->id,
                    'book_id' => $item['book_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            if ($voucherId) {
                VoucherUsage::create([
                    'id' => Str::uuid(),
                    'voucher_id' => $voucherId,
                    'user_id' => $data['user_id'],
                    'order_id' => $order->id,
                    'discount_amount' => $discount,
                    'used_at' => now(),
                ]);
            }

            Cart::where('user_id', $data['user_id'])
                ->whereIn('book_id', $data['selected_book_ids'])
                ->delete();

            $paymentResponse = env('PAYMENT_DUMMY', true)
                ? [
                    'success' => true,
                    'payment_id' => (string) Str::uuid(),
                    'payment_url' => 'http://dummy-payment.local/pay/' . $order->order_number,
                    'status' => 'pending',
                ]
                : $payment->createPayment([
                    'order_id' => $order->id,
                    'user_id' => $data['user_id'],
                    'amount' => $order->final_amount,
                    'payment_method' => $data['payment_method'],
                    'order_number' => $order->order_number,
                ]);

            return ApiResponse::success(
                'Order created and sent to payment',
                [
                    'order' => $order->fresh()->load('items'),
                    'payment' => $paymentResponse,
                ],
                201
            );
        });
    }
}
