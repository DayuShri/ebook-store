<?php

namespace App\Modules\Order\Services;

use App\Modules\Order\Models\Cart;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Order\Models\VoucherUsage;
use App\Services\CatalogClient;
use App\Services\PaymentClient;
use App\Services\VoucherClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Service layer for Order business logic.
 * Per INSTRUCTION.md: Business logic should be in Service layer, not Controller.
 */
class OrderService
{
    public function __construct(
        protected VoucherClient $voucherClient,
        protected CatalogClient $catalogClient,
        protected PaymentClient $paymentClient
    ) {}

    /**
     * Generate checkout preview with pricing.
     *
     * @param string $userId
     * @param array $selectedBookIds
     * @param string|null $voucherCode
     * @return array
     */
    public function preview(string $userId, array $selectedBookIds, ?string $voucherCode = null): array
    {
        $cartItems = Cart::query()
            ->where('user_id', $userId)
            ->whereIn('book_id', $selectedBookIds)
            ->get();

        if ($cartItems->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No selected cart items found',
            ];
        }

        $bookIds = $cartItems->pluck('book_id')->values()->all();
        $books = $this->catalogClient->bulk($bookIds);
        $booksMap = collect($books)->keyBy('book_id');

        $lines = [];
        $subtotal = 0.0;

        foreach ($cartItems as $ci) {
            $b = $booksMap->get($ci->book_id);

            if (!$b) {
                return [
                    'success' => false,
                    'message' => 'Book not found in catalog',
                    'errors' => ['book_id' => [$ci->book_id]],
                ];
            }

            if (!empty($b['is_active']) && $b['is_active'] === false) {
                return [
                    'success' => false,
                    'message' => 'Book is inactive',
                    'errors' => ['book_id' => [$ci->book_id]],
                ];
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

        if (!empty($voucherCode)) {
            $res = $this->voucherClient->validateAndCalculate($voucherCode, $subtotal);

            if (!$res['valid']) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['voucher_code' => [$res['reason']]],
                ];
            }

            $discount = (float) $res['discount_amount'];
            $voucherId = $res['voucher_id'];
        }

        $final = max(0, $subtotal - $discount);

        return [
            'success' => true,
            'data' => [
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($discount, 2),
                'final_amount' => round($final, 2),
                'voucher_id' => $voucherId,
                'items' => $lines,
            ],
        ];
    }

    /**
     * Place order with payment processing.
     *
     * @param string $userId
     * @param array $selectedBookIds
     * @param string|null $voucherCode
     * @param string $paymentMethod
     * @return array
     */
    public function placeOrder(string $userId, array $selectedBookIds, ?string $voucherCode, string $paymentMethod): array
    {
        return DB::transaction(function () use ($userId, $selectedBookIds, $voucherCode, $paymentMethod) {

            $cartItems = Cart::query()
                ->where('user_id', $userId)
                ->whereIn('book_id', $selectedBookIds)
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No selected cart items found',
                ];
            }

            $bookIds = $cartItems->pluck('book_id')->values()->all();
            $books = $this->catalogClient->bulk($bookIds);
            $booksMap = collect($books)->keyBy('book_id');

            $subtotal = 0.0;
            $itemsPayload = [];

            foreach ($cartItems as $ci) {
                $b = $booksMap->get($ci->book_id);

                if (!$b) {
                    return [
                        'success' => false,
                        'message' => 'Book not found in catalog',
                        'errors' => ['book_id' => [$ci->book_id]],
                    ];
                }

                if (!empty($b['is_active']) && $b['is_active'] === false) {
                    return [
                        'success' => false,
                        'message' => 'Book is inactive',
                        'errors' => ['book_id' => [$ci->book_id]],
                    ];
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

            if (!empty($voucherCode)) {
                $res = $this->voucherClient->validateAndCalculate($voucherCode, $subtotal);

                if (!$res['valid']) {
                    return [
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => ['voucher_code' => [$res['reason']]],
                    ];
                }

                $discount = (float) $res['discount_amount'];
                $voucherId = $res['voucher_id'];
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

                // Mark voucher as used via HMVC
                $this->voucherClient->markAsUsed($voucherId);
            }

            Cart::query()
                ->where('user_id', $userId)
                ->whereIn('book_id', $selectedBookIds)
                ->delete();

            // Process payment via HMVC
            $paymentResponse = $this->paymentClient->createPayment([
                'order_id' => $order->id,
                'user_id' => $userId,
                'amount' => round($final, 2),
                'payment_method' => $paymentMethod,
                'order_number' => $order->order_number,
            ]);

            return [
                'success' => true,
                'data' => [
                    'order' => $order->fresh()->load('items'),
                    'payment' => $paymentResponse,
                ],
            ];
        });
    }
}
