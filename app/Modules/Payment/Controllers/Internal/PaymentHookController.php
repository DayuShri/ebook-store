<?php

namespace App\Modules\Payment\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentHookController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create top-up payment
     */
    public function createTopUp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:10000|max:10000000',
            ]);

            $payment = $this->paymentService->createTopUp($request->amount);

            \Log::info('Top-up payment created', [
                'user_id' => auth()->id(),
                'amount' => $request->amount,
                'payment_number' => $payment->payment_number,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'payment_number' => $payment->payment_number,
                    'checkout_url' => $payment->checkout_link,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Top-up payment creation failed', [
                'user_id' => auth()->id(),
                'amount' => $request->amount ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
