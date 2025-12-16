<?php

namespace App\Modules\Auth\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Auth service instance.
     */
    protected AuthService $authService;

    /**
     * Create a new controller instance.
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        \Log::info('HTTP Registration attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);
        
        try {
            $data = $this->authService->register($request->validated());

            \Log::info('HTTP Registration successful', ['email' => $request->email]);

            return response()->json([
                'message' => 'Registration successful',
                'data' => $data,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // This should be caught by the FormRequest, but handle it just in case
            \Log::warning('HTTP Registration validation error', [
                'email' => $request->email,
                'errors' => $e->errors(),
            ]);
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            // Database error (e.g., duplicate email, constraint violation)
            \Log::error('HTTP Registration database error', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            
            // Check if it's a duplicate email error
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                return response()->json([
                    'message' => 'Registration failed',
                    'error' => 'Email address is already registered',
                ], 422);
            }
            
            return response()->json([
                'message' => 'Registration failed',
                'error' => 'Database error occurred',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        } catch (\Exception $e) {
            \Log::error('HTTP Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user and return tokens.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        \Log::info('HTTP Login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);
        
        try {
            $data = $this->authService->login(
                $request->email,
                $request->password
            );

            \Log::info('HTTP Login successful', ['email' => $request->email]);

            return response()->json([
                'message' => 'Login successful',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::warning('HTTP Login failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);
            
            $statusCode = in_array($e->getCode(), [401, 403]) ? $e->getCode() : 500;
            
            return response()->json([
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Refresh access token using refresh token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        try {
            $data = $this->authService->refresh($request->refresh_token);

            return response()->json([
                'message' => 'Token refreshed successfully',
                'data' => $data,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::warning('HTTP Token refresh failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            
            $statusCode = in_array($e->getCode(), [401, 403]) ? $e->getCode() : 500;
            
            return response()->json([
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Logout user and revoke tokens.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return response()->json([
                'message' => 'Logout successful',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user information.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile');
        
        return response()->json([
            'data' => array_merge(
                (new UserResource($user))->toArray($request),
                ['role' => $user->role]
            ),
        ]);
    }
}
