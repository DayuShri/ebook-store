<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\VoucherController;

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

Route::prefix('vouchers')->group(function () {
    Route::get('/', [VoucherController::class, 'index']);
    Route::post('/', [VoucherController::class, 'store']);
    Route::get('/{id}', [VoucherController::class, 'show']);
    Route::patch('/{id}', [VoucherController::class, 'update']);
    Route::delete('/{id}', [VoucherController::class, 'destroy']);

    Route::post('/validate', [VoucherController::class, 'validateVoucher']);
});
