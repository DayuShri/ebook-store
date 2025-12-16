<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Wishlist\Controllers\Http\WishlistController;

Route::prefix('wishlist')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [WishlistController::class, 'index']);
    Route::post('/', [WishlistController::class, 'store']);
    Route::delete('/{bookId}', [WishlistController::class, 'destroy']);
    Route::get('/check/{bookId}', [WishlistController::class, 'check']);
    Route::get('/count', [WishlistController::class, 'count']);
    Route::delete('/', [WishlistController::class, 'clear']);
});