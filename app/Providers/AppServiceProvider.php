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
      $view->with(
    'navbarCategories',
    BookCategory::query()
        ->orderBy('name')
        ->get()
);

    });
    }
}
