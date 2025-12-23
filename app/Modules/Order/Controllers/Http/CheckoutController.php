<?php

namespace App\Modules\Order\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Order\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CheckoutController - thin controller that delegates to OrderService.
 * Per INSTRUCTION.md: Business logic should be in Service layer, not Controller.
 */
class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function preview(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'selected_book_ids' => ['required', 'array', 'min:1'],
            'selected_book_ids.*' => ['uuid'],
            'voucher_code' => ['nullable', 'string'],
        ]);

        $result = $this->orderService->preview(
            $userId,
            $data['selected_book_ids'],
            $data['voucher_code'] ?? null
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Checkout preview generated',
            'data' => $result['data'],
        ]);
    }

    public function placeOrder(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'selected_book_ids' => ['required', 'array', 'min:1'],
            'selected_book_ids.*' => ['uuid'],
            'voucher_code' => ['nullable', 'string'],
            'payment_method' => ['required', 'string', 'max:50'],
        ]);

        try {
            $result = $this->orderService->placeOrder(
                $userId,
                $data['selected_book_ids'],
                $data['voucher_code'] ?? null,
                $data['payment_method']
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? null,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order created and sent to payment',
                'data' => $result['data'],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}

