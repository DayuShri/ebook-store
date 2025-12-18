<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
    }
}
