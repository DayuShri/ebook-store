<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Order\Controllers\Http\CartController;
use App\Modules\Order\Controllers\Http\CheckoutController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/items', [CartController::class, 'store']);
        Route::patch('/items/{bookId}', [CartController::class, 'update']);
        Route::delete('/items/{bookId}', [CartController::class, 'destroy']);
    });

    Route::prefix('checkout')->group(function () {
        Route::post('/preview', [CheckoutController::class, 'preview']);
        Route::post('/place-order', [CheckoutController::class, 'placeOrder']);
    });
});
