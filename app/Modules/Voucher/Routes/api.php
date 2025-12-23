<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Voucher\Controllers\Http\VoucherController;

Route::middleware(['auth:sanctum'])->group(function () {

    // user boleh validate (dipakai checkout)
    Route::post('vouchers/validate', [VoucherController::class, 'validateVoucher']);

    // admin-only CRUD
    Route::middleware(['admin'])->prefix('vouchers')->group(function () {
        Route::get('/', [VoucherController::class, 'index']);
        Route::post('/', [VoucherController::class, 'store']);
        Route::get('/{id}', [VoucherController::class, 'show']);
        Route::patch('/{id}', [VoucherController::class, 'update']);
        Route::delete('/{id}', [VoucherController::class, 'destroy']);
    });
});
