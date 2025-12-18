<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Library\Controllers\Internal\LibraryInternalController;

// HMVC route for granting library items from other modules/services
Route::post('hmvc/library/grant', [LibraryInternalController::class, 'grant']);
