<?php

namespace App\Modules\Payment\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Http\Requests\WalletCreditRequest;
use App\Http\Requests\WalletDeductRequest;
use App\Modules\Payment\Exceptions\InsufficientBalanceException;
use App\Modules\Payment\Services\WalletService;
use Illuminate\Http\JsonResponse;

class WalletHookController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function credit(WalletCreditRequest $request): JsonResponse
    {
        try {
            $transaction = $this->walletService->addBalance(
                $request->user_id,
                $request->amount,
                $request->reference_id,
                $request->description
            );

            $wallet = $this->walletService->getWallet($request->user_id);

            \Log::info('Wallet credit successful', [
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'reference_id' => $request->reference_id,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'transaction' => $transaction,
                    'new_balance' => $wallet->balance,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Wallet credit failed', [
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deduct(WalletDeductRequest $request): JsonResponse
    {
        try {
            $transaction = $this->walletService->deductBalance(
                $request->user_id,
                $request->amount,
                $request->reference_id,
                $request->description
            );

            $wallet = $this->walletService->getWallet($request->user_id);

            \Log::info('Wallet deduction successful', [
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'reference_id' => $request->reference_id,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'transaction' => $transaction,
                    'new_balance' => $wallet->balance,
                ],
            ]);
        } catch (InsufficientBalanceException $e) {
            \Log::warning('Wallet deduction failed - insufficient balance', [
                'user_id' => $request->user_id,
                'amount' => $request->amount,
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (\Exception $e) {
            \Log::error('Wallet deduction failed', [
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get wallet balance for a user
     */
    public function getBalance(string $userId): JsonResponse
    {
        try {
            $wallet = $this->walletService->getWallet($userId);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'balance' => $wallet->balance,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get wallet balance', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get wallet info (balance + basic info)
     */
    public function getWallet(string $userId): JsonResponse
    {
        try {
            $wallet = $this->walletService->getWallet($userId);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $wallet->id,
                    'user_id' => $wallet->user_id,
                    'balance' => $wallet->balance,
                    'created_at' => $wallet->created_at,
                    'updated_at' => $wallet->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get wallet info', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get wallet transaction history
     */
    public function getTransactions(string $userId, int $limit = 20): JsonResponse
    {
        try {
            $wallet = $this->walletService->getWallet($userId);
            $transactions = $wallet->transactions()
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'transactions' => $transactions,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get wallet transactions', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
