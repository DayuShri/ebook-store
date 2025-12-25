<?php

namespace App\Services\Frontend;

use App\Modules\Voucher\Models\Voucher;
use Illuminate\Pagination\LengthAwarePaginator;

class VoucherFrontendService
{
    /**
     * Get all vouchers with pagination (for Admin)
     */
    public function getVouchers(int $perPage = 10): LengthAwarePaginator
    {
        // In a real microservice setup, this might be an API call.
        // For HMVC, we can query the model directly or via Module Service.
        // Querying model directly for simplicity as per project pattern.
        return Voucher::query()
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get active vouchers (for User Checkout)
     */
    public function getActiveVouchers()
    {
        $now = now();
        return Voucher::query()
            ->where('is_active', true)
            ->where('valid_from', '<=', now()->endOfDay())
            ->where('valid_until', '>=', now()->startOfDay())
            ->where(function ($query) {
                $query->whereNull('quota')
                    ->orWhereRaw('used_count < quota');
            })
            ->orderBy('min_purchase_amount', 'asc')
            ->get();
    }

    /**
     * Create a new voucher
     */
    public function createVoucher(array $data): array
    {
        try {
            // Ensure business logic (uppercase code)
            $data['code'] = strtoupper($data['code']);

            // Set defaults if missing
            $data['used_count'] = 0;
            $data['is_active'] = $data['is_active'] ?? true;
            $data['min_purchase_amount'] = $data['min_purchase_amount'] ?? 0;

            Voucher::create($data);

            return ['success' => true, 'message' => 'Voucher created successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to create voucher: ' . $e->getMessage()];
        }
    }

    /**
     * Get specific voucher
     */
    public function getVoucher(string $id): ?Voucher
    {
        return Voucher::find($id);
    }

    /**
     * Update voucher
     */
    public function updateVoucher(string $id, array $data): array
    {
        try {
            $voucher = Voucher::findOrFail($id);

            if (isset($data['code'])) {
                $data['code'] = strtoupper($data['code']);
            }

            // Handle checkbox logic for is_active if it comes from form
            if (!isset($data['is_active'])) {
                // If this method is called from specific update like toggle, handle carefully.
                // Assuming standard update contains all fields or validated data.
            }

            $voucher->update($data);

            return ['success' => true, 'message' => 'Voucher updated successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to update voucher: ' . $e->getMessage()];
        }
    }

    /**
     * Delete voucher
     */
    public function deleteVoucher(string $id): array
    {
        try {
            $voucher = Voucher::findOrFail($id);
            $voucher->delete();

            return ['success' => true, 'message' => 'Voucher deleted successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to delete voucher: ' . $e->getMessage()];
        }
    }
}
