<?php

use App\Modules\Payment\Controllers\Http\WalletController;
use App\Modules\Payment\Controllers\Http\PaymentController;
use Illuminate\Support\Facades\Route;

// Grup rute yang MEMERLUKAN login (Top Up & Wallet)
Route::middleware(['auth:sanctum', 'token.valid'])->group(function () {
    
    // Wallet
    Route::prefix('wallet')->group(function () {
        Route::get('/me', [WalletController::class, 'index']);
        Route::post('/topup-simulation', [WalletController::class, 'topupSimulation']);
    });

    // Top Up
    Route::post('/payment/topup', [PaymentController::class, 'topUp']);

});

// Rute untuk Callback Xendit (HARUS di luar middleware auth)
Route::post('/payment/callback', [PaymentController::class, 'callback']);