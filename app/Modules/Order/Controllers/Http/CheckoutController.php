<?php

namespace App\Modules\Order\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Order\Models\Cart;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Order\Models\VoucherUsage;
use App\Modules\Voucher\Services\VoucherCalculator;
use App\Services\CatalogClient;
use App\Services\PaymentClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function preview(Request $request, VoucherCalculator $voucherCalc, CatalogClient $catalog)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'selected_book_ids' => ['required', 'array', 'min:1'],
            'selected_book_ids.*' => ['uuid'],
            'voucher_code' => ['nullable', 'string'],
        ]);

        $cartItems = Cart::query()
            ->where('user_id', $userId)
            ->whereIn('book_id', $data['selected_book_ids'])
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No selected cart items found',
                'errors' => null,
            ], 422);
        }

        $bookIds = $cartItems->pluck('book_id')->values()->all();

        // IMPORTANT: bulk harus return list item dengan key: book_id, price, discount_percentage, is_active
        $books = $catalog->bulk($bookIds);

        // ✅ keyBy('book_id') (bukan 'id')
        $booksMap = collect($books)->keyBy('book_id');

        $lines = [];
        $subtotal = 0.0;

        foreach ($cartItems as $ci) {
            $b = $booksMap->get($ci->book_id);

            if (!$b) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found in catalog',
                    'errors' => ['book_id' => [$ci->book_id]],
                ], 422);
            }

            if (!empty($b['is_active']) && $b['is_active'] === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book is inactive',
                    'errors' => ['book_id' => [$ci->book_id]],
                ], 422);
            }

            $price = (float) $b['price'];
            $discPct = (float) ($b['discount_percentage'] ?? 0);
            $effectivePrice = $price - ($price * $discPct / 100);

            $lineTotal = $effectivePrice * (int) $ci->quantity;
            $subtotal += $lineTotal;

            $lines[] = [
                'book_id' => $ci->book_id,
                'quantity' => (int) $ci->quantity,
                'unit_price' => round($effectivePrice, 2),
                'line_total' => round($lineTotal, 2),
            ];
        }

        $discount = 0.0;
        $voucherId = null;

        if (!empty($data['voucher_code'])) {
            $res = $voucherCalc->validateAndCalculate($data['voucher_code'], $subtotal);

            if (!$res['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['voucher_code' => [$res['reason']]],
                ], 422);
            }

            $discount = (float) $res['discount_amount'];
            $voucherId = $res['voucher']->id;
        }

        $final = max(0, $subtotal - $discount);

        return response()->json([
            'success' => true,
            'message' => 'Checkout preview generated',
            'data' => [
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($discount, 2),
                'final_amount' => round($final, 2),
                'voucher_id' => $voucherId,
                'items' => $lines,
            ],
        ]);
    }

    public function placeOrder(Request $request, VoucherCalculator $voucherCalc, CatalogClient $catalog, PaymentClient $payment)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'selected_book_ids' => ['required', 'array', 'min:1'],
            'selected_book_ids.*' => ['uuid'],
            'voucher_code' => ['nullable', 'string'],
            'payment_method' => ['required', 'string', 'max:50'],
        ]);

        return DB::transaction(function () use ($data, $userId, $voucherCalc, $catalog, $payment) {

            $cartItems = Cart::query()
                ->where('user_id', $userId)
                ->whereIn('book_id', $data['selected_book_ids'])
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No selected cart items found',
                    'errors' => null,
                ], 422);
            }

            $bookIds = $cartItems->pluck('book_id')->values()->all();
            $books = $catalog->bulk($bookIds);

            // ✅ keyBy('book_id')
            $booksMap = collect($books)->keyBy('book_id');

            $subtotal = 0.0;
            $itemsPayload = [];

            foreach ($cartItems as $ci) {
                $b = $booksMap->get($ci->book_id);

                if (!$b) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Book not found in catalog',
                        'errors' => ['book_id' => [$ci->book_id]],
                    ], 422);
                }

                if (!empty($b['is_active']) && $b['is_active'] === false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Book is inactive',
                        'errors' => ['book_id' => [$ci->book_id]],
                    ], 422);
                }

                $price = (float) $b['price'];
                $discPct = (float) ($b['discount_percentage'] ?? 0);
                $effectivePrice = $price - ($price * $discPct / 100);

                $lineSubtotal = $effectivePrice * (int) $ci->quantity;
                $subtotal += $lineSubtotal;

                $itemsPayload[] = [
                    'book_id' => $ci->book_id,
                    'quantity' => (int) $ci->quantity,
                    'unit_price' => round($effectivePrice, 2),
                    'subtotal' => round($lineSubtotal, 2),
                ];
            }

            $discount = 0.0;
            $voucherId = null;

            if (!empty($data['voucher_code'])) {
                $res = $voucherCalc->validateAndCalculate($data['voucher_code'], $subtotal);

                if (!$res['valid']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => ['voucher_code' => [$res['reason']]],
                    ], 422);
                }

                $discount = (float) $res['discount_amount'];
                $voucherId = $res['voucher']->id;
            }

            $final = max(0, $subtotal - $discount);

            $order = Order::create([
                'id' => (string) Str::uuid(),
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'user_id' => $userId,
                'total_amount' => round($subtotal, 2),
                'discount_amount' => round($discount, 2),
                'final_amount' => round($final, 2),
                'voucher_id' => $voucherId,
                'status' => 'pending',
            ]);

            foreach ($itemsPayload as $it) {
                OrderItem::create([
                    'id' => (string) Str::uuid(),
                    'order_id' => $order->id,
                    'book_id' => $it['book_id'],
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'subtotal' => $it['subtotal'],
                ]);
            }

            if ($voucherId) {
                VoucherUsage::create([
                    'id' => (string) Str::uuid(),
                    'voucher_id' => $voucherId,
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'discount_amount' => round($discount, 2),
                    'used_at' => now(),
                ]);

                // IMPORTANT: Increment global voucher usage count so quota enforcement works
                $voucherCalc->incrementUsage($voucherId);
            }

            Cart::query()
                ->where('user_id', $userId)
                ->whereIn('book_id', $data['selected_book_ids'])
                ->delete();

            // NOTE: PaymentClient kamu harus cocok sama Payment module (atau sementara dummy)
            $paymentResponse = $payment->createPayment([
                'order_id' => $order->id,
                'user_id' => $userId,
                'amount' => round($final, 2),
                'payment_method' => $data['payment_method'],
                'order_number' => $order->order_number,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order created and sent to payment',
                'data' => [
                    'order' => $order->fresh()->load('items'),
                    'payment' => $paymentResponse,
                ],
            ], 201);
        });
    }
}
