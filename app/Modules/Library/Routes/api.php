<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Library\Controllers\Http\LibraryController;
use App\Modules\Library\Controllers\Http\BookFileController;
use App\Modules\Library\Controllers\Http\ViewerController;

Route::prefix('library')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [LibraryController::class, 'index']);
    Route::post('/', [LibraryController::class, 'store']);
    Route::delete('/{id}', [LibraryController::class, 'destroy']);
    Route::post('/revoke', [LibraryController::class, 'revokeByUserAndBook']);
    
    // Create a viewer session (authenticated)
    Route::post('/viewer', [ViewerController::class, 'store']);

    // File meta / download helper
    Route::get('/files/{bookId}/{format}', [BookFileController::class, 'show']);
    Route::post('/files', [BookFileController::class, 'store']);
});

// Stream endpoint uses token, should be accessible without auth
Route::get('library/stream/{token}', [ViewerController::class, 'stream'])->name('library.stream');
