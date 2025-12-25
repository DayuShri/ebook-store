<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Catalog\Controllers\Internal\CatalogInternalController;

Route::prefix('hmvc/catalog')->group(function () {
    Route::post('/prices', [CatalogInternalController::class, 'getBookPrices']);
    Route::get('/books/{id}/full', [CatalogInternalController::class, 'full']);
    Route::get('/books', [CatalogInternalController::class, 'getAllBooks']);
    Route::get('/books/{id}', [CatalogInternalController::class, 'getBookDetail']);
});
