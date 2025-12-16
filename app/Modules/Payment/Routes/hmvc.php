<?php

use App\Modules\Payment\Controllers\Internal\WalletHookController;
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
});
