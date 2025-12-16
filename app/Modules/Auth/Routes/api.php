<?php

use App\Modules\Auth\Controllers\Http\AuthController;
use App\Modules\Auth\Controllers\Http\UserController;
use App\Modules\Auth\Controllers\Http\AdminUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Module API Routes
|--------------------------------------------------------------------------
|
| Here are the authentication routes for the Auth module.
| These routes are prefixed with /api/v1 and use Sanctum middleware
| for protected endpoints.
|
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// Protected routes (requires authentication)
Route::prefix('auth')->middleware(['auth:sanctum', 'token.valid'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// User profile routes
Route::prefix('user')->middleware(['auth:sanctum', 'token.valid'])->group(function () {
    Route::get('/', [UserController::class, 'show']);
    Route::get('/profile', [UserController::class, 'getProfile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::patch('/profile', [UserController::class, 'updateProfile']);
    Route::post('/deactivate', [UserController::class, 'deactivate']);
});

// Admin routes (requires admin role)
Route::prefix('admin')->middleware(['auth:sanctum', 'token.valid', 'admin'])->group(function () {
    // User management
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/statistics', [AdminUserController::class, 'statistics']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::post('/users/{id}/activate', [AdminUserController::class, 'activate']);
    Route::post('/users/{id}/deactivate', [AdminUserController::class, 'deactivate']);
    Route::post('/users/{id}/promote', [AdminUserController::class, 'promoteToAdmin']);
    Route::post('/users/{id}/demote', [AdminUserController::class, 'demoteToUser']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
});
