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
}
