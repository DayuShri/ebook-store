<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display wallet dashboard
     */
    public function index()
    {
        try {
            // Call Payment module's Public API
            $response = Http::withToken(auth()->user()->currentAccessToken()->token)
                ->get(config('app.url') . '/api/v1/wallet/me');

            if ($response->failed()) {
                Log::error('Failed to fetch wallet data', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return view('frontend.wallet.index', [
                    'balance' => 0,
                    'transactions' => [],
                    'cartCount' => $this->cartService->getItemCount(),
                    'error' => 'Gagal memuat data wallet',
                ]);
            }

            $data = $response->json('data');

            return view('frontend.wallet.index', [
                'balance' => $data['balance'] ?? 0,
                'transactions' => $data['transactions'] ?? [],
                'cartCount' => $this->cartService->getItemCount(),
            ]);
        } catch (\Exception $e) {
            Log::error('Exception fetching wallet data', [
                'error' => $e->getMessage(),
            ]);

            return view('frontend.wallet.index', [
                'balance' => 0,
                'transactions' => [],
                'cartCount' => $this->cartService->getItemCount(),
                'error' => 'Terjadi kesalahan saat memuat data wallet',
            ]);
        }
    }

    /**
     * Display top-up form
     */
    public function topupForm()
    {
        try {
            // Call Payment module's Public API
            $response = Http::withToken(auth()->user()->currentAccessToken()->token)
                ->get(config('app.url') . '/api/v1/wallet/me');

            $balance = 0;
            if ($response->successful()) {
                $data = $response->json('data');
                $balance = $data['balance'] ?? 0;
            }

            return view('frontend.wallet.topup', [
                'balance' => $balance,
                'cartCount' => $this->cartService->getItemCount(),
            ]);
        } catch (\Exception $e) {
            Log::error('Exception fetching balance for top-up form', [
                'error' => $e->getMessage(),
            ]);

            return view('frontend.wallet.topup', [
                'balance' => 0,
                'cartCount' => $this->cartService->getItemCount(),
            ]);
        }
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

        try {
            // Call Payment module's Public API to create top-up
            $response = Http::withToken(auth()->user()->currentAccessToken()->token)
                ->post(config('app.url') . '/api/v1/payment/credit', [
                    'amount' => (float) $amount,
                ]);

            if ($response->failed()) {
                $errorMessage = $response->json('message', 'Gagal membuat invoice top-up');
                
                Log::error('Failed to create top-up', [
                    'amount' => $amount,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return redirect()->back()->with('error', $errorMessage);
            }

            $data = $response->json('data');
            $checkoutUrl = $data['checkout_url'] ?? null;

            if ($checkoutUrl) {
                // Redirect to Xendit checkout page
                return redirect()->away($checkoutUrl);
            }

            return redirect()->route('wallet.index')->with('success', 'Top-up berhasil!');
        } catch (\Exception $e) {
            Log::error('Exception creating top-up', [
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
