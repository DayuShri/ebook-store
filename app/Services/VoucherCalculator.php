<?php

namespace App\Services;

use App\Models\Voucher;
use Carbon\Carbon;

class VoucherCalculator
{
    public function validateAndCalculate(string $code, float $subtotal): array
    {
        $now = Carbon::now();

        $voucher = Voucher::query()
            ->where('code', strtoupper($code))
            ->where('is_active', true)
            ->where('valid_from', '<=', $now)
            ->where('valid_until', '>=', $now)
            ->first();

        if (!$voucher) {
            return ['valid' => false, 'reason' => 'VOUCHER_NOT_FOUND'];
        }

        if ($subtotal < (float)$voucher->min_purchase_amount) {
            return ['valid' => false, 'reason' => 'MIN_PURCHASE_NOT_MET'];
        }

        if (!is_null($voucher->quota) && $voucher->used_count >= $voucher->quota) {
            return ['valid' => false, 'reason' => 'QUOTA_EXCEEDED'];
        }

        $discount = 0;

        if ($voucher->discount_type === 'percentage') {
            $discount = $subtotal * ((float)$voucher->discount_value / 100);
            if (!is_null($voucher->max_discount_amount)) {
                $discount = min($discount, (float)$voucher->max_discount_amount);
            }
        } else if ($voucher->discount_type === 'fixed') {
            $discount = (float)$voucher->discount_value;
        }

        $discount = min($discount, $subtotal);

        return [
            'valid' => true,
            'voucher' => $voucher,
            'discount_amount' => round($discount, 2),
        ];
    }
}
