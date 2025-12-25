<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Modules\Catalog\Models\BookCategory;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Modules\Review_Reading\Contracts\LibraryAccessService::class,
            \App\Modules\Library\Services\LibraryAccessServiceImpl::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('frontend.layouts.*', function ($view) {
            // Navbar categories
            $view->with(
                'navbarCategories',
                BookCategory::query()
                    ->orderBy('name')
                    ->get()
            );

            // Wallet balance for authenticated users
            $walletBalance = 0;
            $cartCount = 0;
            
            if (auth()->check()) {
                try {
                    // For backend-to-backend, call service directly instead of HTTP
                    // This avoids timeout issues with self-referential HTTP calls
                    $walletService = app(\App\Modules\Payment\Services\WalletService::class);
                    $wallet = $walletService->getWallet(auth()->id());
                    $walletBalance = $wallet->balance ?? 0;
                } catch (\Exception $e) {
                    // Silently fail - wallet balance will remain 0
                    \Log::debug('Failed to fetch wallet balance for navbar', [
                        'error' => $e->getMessage()
                    ]);
                }

                // Get cart count from database
                try {
                    $cartCount = \App\Modules\Order\Models\Cart::where('user_id', auth()->id())->count();
                } catch (\Exception $e) {
                    \Log::debug('Failed to fetch cart count for navbar', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $view->with('walletBalance', $walletBalance);
            $view->with('cartCount', $cartCount);
        });
    }
}
