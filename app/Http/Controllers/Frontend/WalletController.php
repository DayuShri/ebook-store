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
        // Determine the amount from preset or custom
        $amount = $request->input('custom_amount') ?: $request->input('amount');
        
        $request->merge(['amount' => $amount]);
        
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:10000000',
        ]);

        $result = $this->walletService->topUp((float) $amount);

        if ($result['success']) {
            // If there's a checkout URL, redirect to Xendit
            if (!empty($result['redirect']) && !empty($result['checkout_url'])) {
                return redirect()->away($result['checkout_url']);
            }
            
            return redirect()->route('wallet.index')->with('success', 'Top-up berhasil!');
        }

        return redirect()->back()->with('error', $result['message']);
    }
}
