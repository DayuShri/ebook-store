<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Catalog\Controllers\Http\BookController;
use App\Modules\Catalog\Controllers\Http\CategoryController;

/*
|--------------------------------------------------------------------------
| Catalog Public & Admin Routes
|--------------------------------------------------------------------------
*/

// Public / User routes (READ ONLY)
Route::prefix('catalog')->middleware(['auth:sanctum', 'token.valid'])->group(function () {
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{id}', [BookController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'books']);
});

// Admin routes (CRUD)
Route::prefix('admin/catalog')
    ->middleware(['auth:sanctum', 'token.valid', 'admin'])
    ->group(function () {
        Route::post('/books', [BookController::class, 'store']);
        Route::put('/books/{id}', [BookController::class, 'update']);
        Route::delete('/books/{id}', [BookController::class, 'destroy']);

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    });
