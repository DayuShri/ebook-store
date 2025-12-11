<?php

use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});

// Protected routes (requires authentication)
Route::prefix('v1')->middleware(['auth:sanctum', 'token.valid'])->group(function () {
    // Authentication routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // User profile routes
    Route::get('/user', [UserController::class, 'show']);
    Route::get('/user/profile', [UserController::class, 'getProfile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::patch('/user/profile', [UserController::class, 'updateProfile']);
    Route::post('/user/deactivate', [UserController::class, 'deactivate']);
});

// Admin routes (requires admin role)
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'token.valid', 'admin'])->group(function () {
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
