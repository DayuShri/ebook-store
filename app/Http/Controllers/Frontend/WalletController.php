<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\WalletFrontendService;
use App\Services\Frontend\CartService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected WalletFrontendService $walletService;
    protected CartService $cartService;

    public function __construct(WalletFrontendService $walletService, CartService $cartService)
    {
        $this->walletService = $walletService;
        $this->cartService = $cartService;
    }

    /**
     * Display wallet dashboard
     */
    public function index()
    {
        $wallet = $this->walletService->getWallet();
        $transactions = $this->walletService->getTransactionHistory(20);

        return view('frontend.wallet.index', [
            'balance' => $wallet['balance'],
            'transactions' => $transactions,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Display top-up form
     */
    public function topupForm()
    {
        return view('frontend.wallet.topup', [
            'balance' => $this->walletService->getBalance(),
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Process top-up
     */
    public function topup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:10000000',
            'method' => 'required|in:bank_transfer,credit_card,qris',
        ]);

        $result = $this->walletService->topUp(
            (float) $request->input('amount'),
            $request->input('method')
        );

        if ($result['success']) {
            return redirect()->route('wallet.index')->with('success', 'Top-up berhasil! Saldo Anda sekarang Rp ' . number_format($result['balance'], 0, ',', '.'));
        }

        return redirect()->back()->with('error', $result['message']);
    }
}
