<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\VoucherController;

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Note: Module routes are automatically loaded by ModuleServiceProvider
| from app/Modules/{Module}/Routes/api.php and app/Modules/{Module}/Routes/hmvc.php
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Library streaming endpoint
Route::get('/v1/library/stream/{token}', [\App\Modules\Library\Controllers\Api\LibraryStreamController::class, 'stream'])
    ->name('library.stream');

// Order Service Routes (punyamu)
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


