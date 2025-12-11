<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\RefreshToken;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'role' => 'user',
                'is_active' => true,
            ]);

            // Create user profile
            UserProfile::create([
                'user_id' => $user->id,
                'full_name' => $request->full_name,
                'date_of_birth' => $request->date_of_birth,
                'phone_number' => $request->phone_number,
            ]);

            // Generate tokens
            $accessToken = $user->createToken('access_token')->plainTextToken;
            $refreshToken = $this->generateRefreshToken($user);

            DB::commit();

            return response()->json([
                'message' => 'Registration successful',
                'data' => [
                    'user' => new UserResource($user->load('profile')),
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
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
        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Verify credentials
        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Account is inactive. Please contact support.',
            ], 403);
        }

        try {
            // Update last login
            $user->update(['last_login_at' => now()]);

            // Generate tokens
            $accessToken = $user->createToken('access_token')->plainTextToken;
            $refreshToken = $this->generateRefreshToken($user);

            return response()->json([
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($user->load('profile')),
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'Bearer',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 500);
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

        // Find refresh token
        $refreshToken = RefreshToken::where('token', $request->refresh_token)->first();

        if (!$refreshToken) {
            return response()->json([
                'message' => 'Invalid refresh token',
            ], 401);
        }

        // Validate token
        if (!$refreshToken->isValid()) {
            return response()->json([
                'message' => 'Refresh token has expired or been revoked',
            ], 401);
        }

        try {
            $user = $refreshToken->user;

            // Check if user is active
            if (!$user->is_active) {
                return response()->json([
                    'message' => 'Account is inactive',
                ], 403);
            }

            // Generate new access token
            $accessToken = $user->createToken('access_token')->plainTextToken;

            return response()->json([
                'message' => 'Token refreshed successfully',
                'data' => [
                    'access_token' => $accessToken,
                    'token_type' => 'Bearer',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token refresh failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout user and revoke tokens.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Revoke all access tokens
            $user->tokens()->delete();

            // Revoke all refresh tokens
            $user->refreshTokens()->update(['is_revoked' => true]);

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
        return response()->json([
            'data' => new UserResource($request->user()->load('profile')),
        ]);
    }

    /**
     * Generate a refresh token for the user.
     */
    private function generateRefreshToken(User $user): string
    {
        $token = Str::random(64);

        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addDays(30),
            'is_revoked' => false,
        ]);

        return $token;
    }
}
