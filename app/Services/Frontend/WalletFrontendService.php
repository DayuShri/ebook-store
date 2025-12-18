<?php

namespace App\Services\Frontend;

/**
 * Wallet Frontend Service - Mock Data Layer
 * 
 * This service provides mock wallet functionality.
 * Uses session storage for wallet state.
 */
class WalletFrontendService
{
    private const SESSION_KEY = 'user_wallet';

    /**
     * Get current wallet balance
     */
    public function getBalance(): float
    {
        $wallet = $this->getWallet();
        return $wallet['balance'];
    }

    /**
     * Get wallet with transaction history
     */
    public function getWallet(): array
    {
        return session(self::SESSION_KEY, [
            'id' => 'wallet-001',
            'balance' => 500000, // Starting balance for mock
            'transactions' => $this->getMockTransactions(),
        ]);
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(int $limit = 20): array
    {
        $wallet = $this->getWallet();
        return array_slice($wallet['transactions'], 0, $limit);
    }

    /**
     * Top up wallet (mock)
     */
    public function topUp(float $amount, string $method): array
    {
        if ($amount < 10000) {
            return ['success' => false, 'message' => 'Minimal top-up Rp 10.000'];
        }
        if ($amount > 10000000) {
            return ['success' => false, 'message' => 'Maksimal top-up Rp 10.000.000'];
        }

        $wallet = $this->getWallet();
        $balanceBefore = $wallet['balance'];
        $wallet['balance'] += $amount;

        // Add transaction record
        $transaction = [
            'id' => 'txn-' . uniqid(),
            'type' => 'top_up',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $wallet['balance'],
            'description' => 'Top-up via ' . $this->getMethodLabel($method),
            'reference_id' => 'TOPUP-' . strtoupper(substr(uniqid(), -8)),
            'created_at' => now()->toDateTimeString(),
        ];
        
        array_unshift($wallet['transactions'], $transaction);
        session([self::SESSION_KEY => $wallet]);

        return [
            'success' => true,
            'message' => 'Top-up berhasil',
            'balance' => $wallet['balance'],
            'transaction' => $transaction,
        ];
    }

    /**
     * Pay from wallet (mock)
     */
    public function pay(float $amount, string $orderId, string $description = 'Pembelian Buku'): array
    {
        $wallet = $this->getWallet();
        
        if ($wallet['balance'] < $amount) {
            return [
                'success' => false, 
                'message' => 'Saldo tidak mencukupi',
                'balance' => $wallet['balance'],
                'required' => $amount,
            ];
        }

        $balanceBefore = $wallet['balance'];
        $wallet['balance'] -= $amount;

        // Add transaction record
        $transaction = [
            'id' => 'txn-' . uniqid(),
            'type' => 'payment',
            'amount' => -$amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $wallet['balance'],
            'description' => $description,
            'reference_id' => $orderId,
            'created_at' => now()->toDateTimeString(),
        ];
        
        array_unshift($wallet['transactions'], $transaction);
        session([self::SESSION_KEY => $wallet]);

        return [
            'success' => true,
            'message' => 'Pembayaran berhasil',
            'balance' => $wallet['balance'],
            'transaction' => $transaction,
        ];
    }

    /**
     * Check if balance is sufficient
     */
    public function hasEnoughBalance(float $amount): bool
    {
        return $this->getBalance() >= $amount;
    }

    /**
     * Get payment method label
     */
    private function getMethodLabel(string $method): string
    {
        return match($method) {
            'bank_transfer' => 'Transfer Bank',
            'credit_card' => 'Kartu Kredit',
            'qris' => 'QRIS',
            default => 'Metode Lain',
        };
    }

    /**
     * Mock transaction history
     */
    private function getMockTransactions(): array
    {
        return [
            [
                'id' => 'txn-mock-001',
                'type' => 'top_up',
                'amount' => 200000,
                'balance_before' => 300000,
                'balance_after' => 500000,
                'description' => 'Top-up via Transfer Bank',
                'reference_id' => 'TOPUP-ABC12345',
                'created_at' => now()->subDays(2)->toDateTimeString(),
            ],
            [
                'id' => 'txn-mock-002',
                'type' => 'payment',
                'amount' => -89000,
                'balance_before' => 389000,
                'balance_after' => 300000,
                'description' => 'Pembelian Buku: Atomic Habits',
                'reference_id' => 'ORD-XYZ98765',
                'created_at' => now()->subDays(5)->toDateTimeString(),
            ],
            [
                'id' => 'txn-mock-003',
                'type' => 'top_up',
                'amount' => 100000,
                'balance_before' => 289000,
                'balance_after' => 389000,
                'description' => 'Top-up via QRIS',
                'reference_id' => 'TOPUP-DEF67890',
                'created_at' => now()->subDays(10)->toDateTimeString(),
            ],
            [
                'id' => 'txn-mock-004',
                'type' => 'refund',
                'amount' => 45000,
                'balance_before' => 244000,
                'balance_after' => 289000,
                'description' => 'Refund pesanan ORD-OLD12345',
                'reference_id' => 'REF-OLD12345',
                'created_at' => now()->subDays(15)->toDateTimeString(),
            ],
        ];
    }
}
