<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Catalog\Controllers\Internal\CatalogInternalController;

Route::prefix('hmvc/catalog')->group(function () {
    Route::get('/books/{id}/price', [CatalogInternalController::class, 'price']);
    Route::get('/books/{id}/exists', [CatalogInternalController::class, 'exists']);
    Route::get('/books/{id}/basic', [CatalogInternalController::class, 'basic']);
    Route::post('/books/bulk-price', [CatalogInternalController::class, 'bulkPrice']);
});
