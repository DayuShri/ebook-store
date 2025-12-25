<?php

namespace App\Modules\Voucher\Services;

use App\Modules\Voucher\Models\Voucher;

class VoucherCalculator
{
    public function validateAndCalculate(string $code, float $subtotal): array
    {
        $code = strtoupper(trim($code));

        $voucher = Voucher::query()
            ->where('code', $code)
            ->first();

        if (!$voucher) {
            return ['valid' => false, 'reason' => 'Voucher not found'];
        }

        if (!$voucher->is_active) {
            return ['valid' => false, 'reason' => 'Voucher is inactive'];
        }

        $now = now();
        if ($now->lt($voucher->valid_from) || $now->gt($voucher->valid_until)) {
            return ['valid' => false, 'reason' => 'Voucher is expired or not active yet'];
        }

        $min = (float) ($voucher->min_purchase_amount ?? 0);
        if ($subtotal < $min) {
            return ['valid' => false, 'reason' => "Minimum purchase is {$min}"];
        }

        if (!is_null($voucher->quota)) {
            if ((int) $voucher->used_count >= (int) $voucher->quota) {
                return ['valid' => false, 'reason' => 'Voucher quota exceeded'];
            }
        }

        $discountAmount = 0.0;

        if ($voucher->discount_type === 'percentage') {
            $pct = (float) $voucher->discount_value;
            $discountAmount = $subtotal * ($pct / 100);
        } elseif ($voucher->discount_type === 'fixed') {
            $discountAmount = (float) $voucher->discount_value;
        } else {
            return ['valid' => false, 'reason' => 'Invalid discount_type'];
        }

        if (!is_null($voucher->max_discount_amount)) {
            $max = (float) $voucher->max_discount_amount;
            $discountAmount = min($discountAmount, $max);
        }

        $discountAmount = max(0, min($discountAmount, $subtotal));

        return [
            'valid' => true,
            'voucher' => $voucher,
            'discount_amount' => round($discountAmount, 2),
        ];
    }

    public function incrementUsage(string $voucherId): void
    {
        Voucher::query()
            ->where('id', $voucherId)
            ->increment('used_count');
    }
}
