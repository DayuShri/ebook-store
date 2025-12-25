<?php

use App\Modules\Voucher\Controllers\Internal\VoucherHookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Voucher Module HMVC Routes
|--------------------------------------------------------------------------
|
| IMPORTANT: HMVC routes are NOT auto-prefixed by ModuleServiceProvider.
| You MUST define your own prefix here (e.g., 'hmvc/voucher').
|
*/

Route::prefix('hmvc/voucher')->group(function () {
    Route::post('/validate', [VoucherHookController::class, 'validate']);
    Route::post('/use', [VoucherHookController::class, 'markAsUsed']);
});
