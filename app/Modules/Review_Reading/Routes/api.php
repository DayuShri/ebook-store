<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Review_Reading\Controllers\Http\ReadingController;
use App\Modules\Review_Reading\Controllers\Http\ReviewController;

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/reading/start', [ReadingController::class, 'start']);
    Route::post('/reading/finish', [ReadingController::class, 'finish']);
    Route::post('/reading/progress', [ReadingController::class, 'updateProgress']);
    Route::get('/reading/progress/{book_id}', [ReadingController::class, 'progress']);
    Route::post('/reading/review', [ReviewController::class, 'store']);
    Route::get('/reading/reviews', [ReviewController::class, 'userReviews']);
    
    // Review endpoints
    
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::post('/reviews/helpful', [ReviewController::class, 'helpful']);
    Route::get('/reviews/{book_id}', [ReviewController::class, 'index']);
    Route::put('/reviews/{review_id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review_id}', [ReviewController::class, 'destroy']);
    


});
