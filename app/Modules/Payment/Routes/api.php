<?php

use App\Modules\Payment\Controllers\Http\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('wallet')->middleware(['auth:sanctum', 'token.valid'])->group(function () {
    Route::get('/me', [WalletController::class, 'index']);
    Route::post('/topup-simulation', [WalletController::class, 'topupSimulation']);
});
