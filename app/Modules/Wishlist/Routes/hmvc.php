<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Wishlist\Controllers\Internal\WishlistInternalController;

Route::prefix('hmvc/wishlist')->group(function () {
    Route::post('/check', [WishlistInternalController::class, 'check']);
    Route::get('/count/{userId}', [WishlistInternalController::class, 'count']);
    Route::get('/user/{userId}', [WishlistInternalController::class, 'getUserWishlist']);
});