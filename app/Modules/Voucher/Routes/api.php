<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Voucher\Controllers\Http\VoucherController;

Route::middleware(['auth:sanctum'])->group(function () {

    // user boleh validate (dipakai checkout)
    Route::post('v1/vouchers/validate', [VoucherController::class, 'validateVoucher']);

    // admin-only CRUD
    Route::middleware(['admin'])->prefix('v1/vouchers')->group(function () {
        Route::get('/', [VoucherController::class, 'index']);
        Route::post('/', [VoucherController::class, 'store']);
        Route::get('/{id}', [VoucherController::class, 'show']);
        Route::patch('/{id}', [VoucherController::class, 'update']);
        Route::delete('/{id}', [VoucherController::class, 'destroy']);
    });
});
