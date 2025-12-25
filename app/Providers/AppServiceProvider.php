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
            if (auth()->check()) {
                try {
                    $token = $this->getAuthToken();
                    if ($token) {
                        $response = \Illuminate\Support\Facades\Http::withToken($token)
                            ->get(config('app.url') . '/api/v1/wallet/me');
                        
                        if ($response->successful()) {
                            $data = $response->json('data');
                            $walletBalance = $data['balance'] ?? 0;
                        }
                    }
                } catch (\Exception $e) {
                    // Silently fail - wallet balance will remain 0
                    \Log::debug('Failed to fetch wallet balance for navbar', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $view->with('walletBalance', $walletBalance);
        });
    }

    /**
     * Get authentication token for API calls
     */
    protected function getAuthToken(): ?string
    {
        // Try to get token from current access token (API auth)
        if (auth()->user()->currentAccessToken()) {
            return auth()->user()->currentAccessToken()->token;
        }
        
        // Fall back to session token (web auth)
        return session('api_token');
    }
}
