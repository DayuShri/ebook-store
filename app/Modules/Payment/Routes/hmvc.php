<?php

use App\Modules\Payment\Controllers\Internal\WalletHookController;
use App\Modules\Payment\Controllers\Internal\PaymentHookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payment Module HMVC Routes
|--------------------------------------------------------------------------
|
| IMPORTANT: HMVC routes are NOT auto-prefixed by ModuleServiceProvider.
| You MUST define your own prefix here (e.g., 'hmvc/wallet').
|
*/

Route::prefix('hmvc/wallet')->group(function () {
    Route::post('/credit', [WalletHookController::class, 'credit']);
    Route::post('/deduct', [WalletHookController::class, 'deduct']);
    Route::get('/{userId}/balance', [WalletHookController::class, 'getBalance']);
    Route::get('/{userId}/info', [WalletHookController::class, 'getWallet']);
    Route::get('/{userId}/transactions', [WalletHookController::class, 'getTransactions']);
});

Route::prefix('hmvc/payment')->group(function () {
    Route::post('/topup', [PaymentHookController::class, 'createTopUp']);
});


