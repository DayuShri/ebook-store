<?php

namespace App\Modules\Payment\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function topUp(Request $request)
    {
        // Validasi input minimal top up Rp 10.000
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        try {
            $payment = $this->paymentService->createTopUp($request->amount);

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice Top Up berhasil dibuat',
                'data' => [
                    'payment_number' => $payment->payment_number,
                    'amount' => $payment->amount,
                    'checkout_url' => $payment->checkout_link, // Link untuk bayar di browser
                    'status' => $payment->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        try {
            $data = $request->all();
            $this->paymentService->handleCallback($data);

            return response()->json(['status' => 'OK']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}