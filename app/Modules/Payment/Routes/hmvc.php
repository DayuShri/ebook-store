<?php

use App\Modules\Payment\Controllers\Internal\WalletHookController;
use Illuminate\Support\Facades\Route;

Route::prefix('hmvc/wallet')->group(function () {
    Route::post('/credit', [WalletHookController::class, 'credit']);
    Route::post('/deduct', [WalletHookController::class, 'deduct']);
});
