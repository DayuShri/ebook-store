<?php

namespace App\Http\Controllers\Api;

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
        try {
            $data = $this->authService->register($request->validated());

            return response()->json([
                'message' => 'Registration successful',
                'data' => $data,
            ], 201);
        } catch (\Exception $e) {
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
        try {
            $data = $this->authService->login(
                $request->email,
                $request->password
            );

            return response()->json([
                'message' => 'Login successful',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            
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
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            
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
