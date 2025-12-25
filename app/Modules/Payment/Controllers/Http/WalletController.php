<?php

namespace App\Modules\Payment\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Http\Requests\TopupRequest;
use App\Modules\Payment\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $wallet = $this->walletService->getWallet($user->id);

            $transactions = $wallet->transactions()
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $wallet->balance,
                    'transactions' => $transactions,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve wallet information', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function topupSimulation(TopupRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $transaction = $this->walletService->addBalance(
                $user->id,
                $request->amount,
                null,
                'Top-up simulation'
            );

            $wallet = $this->walletService->getWallet($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Top-up successful',
                'data' => [
                    'transaction' => $transaction,
                    'new_balance' => $wallet->balance,
                ],
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Top-up simulation failed', [
                'user_id' => $request->user()->id,
                'amount' => $request->amount,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
