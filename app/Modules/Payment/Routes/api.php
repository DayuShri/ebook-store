<?php

use App\Modules\Payment\Controllers\Http\WalletController;
use App\Modules\Payment\Controllers\Http\PaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/payment/callback', [PaymentController::class, 'callback']);

Route::post('/payments', [PaymentController::class, 'store']);

// login (Sanctum)
Route::middleware(['auth:sanctum', 'token.valid'])->group(function () {
    
    // Wallet (Melihat saldo & simulasi)
    Route::prefix('wallet')->group(function () {
        Route::get('/me', [WalletController::class, 'index']);
        Route::post('/topup-simulation', [WalletController::class, 'topupSimulation']);
    });

    // Top Up (Proses pembuatan invoice Xendit)
    Route::post('/payment/topup', [PaymentController::class, 'topUp']);
});