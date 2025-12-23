<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

<<<<<<< HEAD
class PaymentClient
{
    public function createPayment(array $payload): array
    {
        $url = config('services.payment.base_url') . '/api/payments';

        $resp = Http::acceptJson()
            ->timeout(5)
            ->post($url, $payload);

        if ($resp->failed()) {
            abort(503, 'Payment service unavailable');
        }

        return $resp->json() ?? [];
=======
/**
 * HMVC Client for inter-module communication with Payment module.
 * Per INSTRUCTION.md: Use HMVC API for communication between modules.
 * 
 * NOTE: Uses localhost for internal HMVC calls to avoid self-referential
 * HTTP issues when app.url points to ngrok or external URL.
 */
class PaymentClient
{
    protected string $baseUrl;

    public function __construct()
    {
        // Use localhost for internal HMVC calls (avoid ngrok self-referential issues)
        $this->baseUrl = config('hmvc.internal_url', 'http://127.0.0.1:8000');
    }

    /**
     * Create a payment by deducting from wallet.
     * Calls Payment module's deduct endpoint.
     *
     * @param array $data Contains order_id, user_id, amount, payment_method
     * @return array Payment result
     * @throws \Exception if payment fails
     */
    public function createPayment(array $data): array
    {
        $response = Http::post($this->baseUrl . '/api/v1/payment/deduct', [
            'order_id' => $data['order_id'],
            'user_id' => $data['user_id'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
        ]);

        if ($response->failed()) {
            $message = $response->json('message') ?? 'Payment failed';
            \Log::error('PaymentClient createPayment failed', [
                'data' => $data,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception($message);
        }

        return $response->json('data') ?? [];
    }

    /**
     * Credit wallet balance via HMVC.
     *
     * @param string $userId
     * @param float $amount
     * @param string|null $referenceId
     * @param string|null $description
     * @return array
     */
    public function creditWallet(string $userId, float $amount, ?string $referenceId = null, ?string $description = null): array
    {
        $response = Http::post($this->baseUrl . '/hmvc/wallet/credit', [
            'user_id' => $userId,
            'amount' => $amount,
            'reference_id' => $referenceId,
            'description' => $description,
        ]);

        if ($response->failed()) {
            \Log::error('PaymentClient creditWallet failed', [
                'user_id' => $userId,
                'amount' => $amount,
                'status' => $response->status(),
            ]);
            return ['success' => false];
        }

        return $response->json();
    }

    /**
     * Deduct wallet balance via HMVC.
     *
     * @param string $userId
     * @param float $amount
     * @param string|null $referenceId
     * @param string|null $description
     * @return array
     */
    public function deductWallet(string $userId, float $amount, ?string $referenceId = null, ?string $description = null): array
    {
        $response = Http::post($this->baseUrl . '/hmvc/wallet/deduct', [
            'user_id' => $userId,
            'amount' => $amount,
            'reference_id' => $referenceId,
            'description' => $description,
        ]);

        if ($response->failed()) {
            $message = $response->json('message') ?? 'Wallet deduction failed';
            throw new \Exception($message);
        }

        return $response->json();
>>>>>>> 2347f10c6476bdca24e206f24d6746d0805d9124
    }
}
