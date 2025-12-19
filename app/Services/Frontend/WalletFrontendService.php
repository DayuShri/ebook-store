<?php

namespace App\Services\Frontend;

use App\Modules\Payment\Services\PaymentService;
use App\Modules\Payment\Services\WalletService;
use Illuminate\Support\Facades\Auth;

/**
 * Wallet Frontend Service - Real API Integration
 * 
 * This service integrates with the Payment module for real wallet and top-up operations.
 */
class WalletFrontendService
{
    protected WalletService $walletService;
    protected PaymentService $paymentService;

    public function __construct(WalletService $walletService, PaymentService $paymentService)
    {
        $this->walletService = $walletService;
        $this->paymentService = $paymentService;
    }

    /**
     * Get current wallet balance
     */
    public function getBalance(): float
    {
        $user = Auth::user();
        if (!$user) {
            return 0;
        }

        $wallet = $this->walletService->getWallet($user->id);
        return (float) $wallet->balance;
    }

    /**
     * Get wallet with transaction history
     */
    public function getWallet(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [
                'id' => null,
                'balance' => 0,
                'transactions' => [],
            ];
        }

        $wallet = $this->walletService->getWallet($user->id);
        
        return [
            'id' => $wallet->id,
            'balance' => (float) $wallet->balance,
            'transactions' => $this->getTransactionHistory(20),
        ];
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(int $limit = 20): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $wallet = $this->walletService->getWallet($user->id);
        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $transactions->map(function ($tx) {
            return [
                'id' => $tx->id,
                'type' => $tx->transaction_type,
                'amount' => $tx->transaction_type === 'payment' ? -$tx->amount : $tx->amount,
                'balance_before' => $tx->balance_before,
                'balance_after' => $tx->balance_after,
                'description' => $tx->description,
                'reference_id' => $tx->reference_id,
                'created_at' => $tx->created_at->toDateTimeString(),
            ];
        })->toArray();
    }

    /**
     * Top up wallet via Xendit
     * Returns checkout URL for redirect
     */
    public function topUp(float $amount, ?string $method = null): array
    {
        if ($amount < 10000) {
            return ['success' => false, 'message' => 'Minimal top-up Rp 10.000'];
        }
        if ($amount > 10000000) {
            return ['success' => false, 'message' => 'Maksimal top-up Rp 10.000.000'];
        }

        try {
            $payment = $this->paymentService->createTopUp($amount);

            return [
                'success' => true,
                'message' => 'Invoice berhasil dibuat',
                'redirect' => true,
                'checkout_url' => $payment->checkout_link,
                'payment_number' => $payment->payment_number,
            ];
        } catch (\Exception $e) {
            \Log::error('Top-up failed', [
                'user_id' => Auth::id(),
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Gagal membuat invoice: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Pay from wallet
     */
    public function pay(float $amount, string $orderId, string $description = 'Pembelian Buku'): array
    {
        $user = Auth::user();
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak terautentikasi',
            ];
        }

        try {
            $wallet = $this->walletService->getWallet($user->id);
            
            if ($wallet->balance < $amount) {
                return [
                    'success' => false, 
                    'message' => 'Saldo tidak mencukupi',
                    'balance' => $wallet->balance,
                    'required' => $amount,
                ];
            }

            $transaction = $this->walletService->deductBalance(
                $user->id,
                $amount,
                $orderId,
                $description
            );

            $updatedWallet = $this->walletService->getWallet($user->id);

            return [
                'success' => true,
                'message' => 'Pembayaran berhasil',
                'balance' => $updatedWallet->balance,
                'transaction' => $transaction,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if balance is sufficient
     */
    public function hasEnoughBalance(float $amount): bool
    {
        return $this->getBalance() >= $amount;
    }
}
