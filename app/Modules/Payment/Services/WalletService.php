<?php

namespace App\Modules\Payment\Services;

use App\Modules\Payment\Exceptions\InsufficientBalanceException;
use App\Modules\Payment\Models\Wallet;
use App\Modules\Payment\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Get wallet for a user, create if not exists.
     *
     * @param string $userId
     * @return Wallet
     */
    public function getWallet(string $userId): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0]
        );
    }

    /**
     * Add balance to user's wallet (for top-ups).
     *
     * @param string $userId
     * @param float $amount
     * @param string|null $referenceId
     * @param string|null $description
     * @return WalletTransaction
     * @throws \Exception
     */
    public function addBalance(
        string $userId,
        float $amount,
        ?string $referenceId = null,
        ?string $description = null
    ): WalletTransaction {
        return DB::transaction(function () use ($userId, $amount, $referenceId, $description) {
            $wallet = $this->getWallet($userId);

            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update wallet balance
            $wallet->balance = $balanceAfter;
            $wallet->save();

            // Create transaction record
            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'top_up',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);

            return $transaction;
        });
    }

    /**
     * Deduct balance from user's wallet (for purchases).
     *
     * @param string $userId
     * @param float $amount
     * @param string|null $referenceId
     * @param string|null $description
     * @return WalletTransaction
     * @throws InsufficientBalanceException
     * @throws \Exception
     */
    public function deductBalance(
        string $userId,
        float $amount,
        ?string $referenceId = null,
        ?string $description = null
    ): WalletTransaction {
        return DB::transaction(function () use ($userId, $amount, $referenceId, $description) {
            $wallet = $this->getWallet($userId);

            // Lock the wallet row to prevent race conditions
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

            if (!$wallet) {
                $wallet = $this->getWallet($userId);
            }

            // Check if sufficient balance
            if ($wallet->balance < $amount) {
                throw new InsufficientBalanceException(
                    "Insufficient balance. Current balance: {$wallet->balance}, Required: {$amount}"
                );
            }

            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore - $amount;

            // Update wallet balance
            $wallet->balance = $balanceAfter;
            $wallet->save();

            // Create transaction record
            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'payment',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);

            return $transaction;
        });
    }
}
