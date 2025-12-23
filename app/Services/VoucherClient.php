<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * HMVC Client for inter-module communication with Voucher module.
 * Per INSTRUCTION.md: Use HMVC API for communication between modules.
 * 
 * NOTE: Uses localhost for internal HMVC calls to avoid self-referential
 * HTTP issues when app.url points to ngrok or external URL.
 */
class VoucherClient
{
    protected string $baseUrl;

    public function __construct()
    {
        // Use localhost for internal HMVC calls (avoid ngrok self-referential issues)
        $internalUrl = config('hmvc.internal_url', 'http://127.0.0.1:8000');
        $this->baseUrl = $internalUrl . '/hmvc/voucher';
    }

    /**
     * Validate a voucher and calculate discount.
     *
     * @param string $code Voucher code
     * @param float $subtotal Cart subtotal
     * @return array Contains 'valid', 'voucher_id', 'discount_amount', or 'reason'
     */
    public function validateAndCalculate(string $code, float $subtotal): array
    {
        $response = Http::post($this->baseUrl . '/validate', [
            'code' => $code,
            'subtotal' => $subtotal,
        ]);

        if ($response->failed()) {
            return [
                'valid' => false,
                'reason' => $response->json('message') ?? 'Voucher validation failed',
            ];
        }

        $data = $response->json('data');
        return [
            'valid' => true,
            'voucher_id' => $data['voucher_id'] ?? null,
            'discount_amount' => $data['discount_amount'] ?? 0,
        ];
    }

    /**
     * Mark a voucher as used (increment used_count).
     *
     * @param string $voucherId
     * @return bool
     */
    public function markAsUsed(string $voucherId): bool
    {
        $response = Http::post($this->baseUrl . '/use', [
            'voucher_id' => $voucherId,
        ]);

        return $response->successful();
    }
}
