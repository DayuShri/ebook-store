<?php

namespace App\Modules\Auth\Services;

use App\Http\Resources\UserResource;
use App\Models\RefreshToken;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Register a new user.
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function register(array $data): array
    {
        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'email' => $data['email'],
                'password_hash' => Hash::make($data['password']),
                'role' => 'user',
                'is_active' => true,
            ]);

            // Create user profile
            UserProfile::create([
                'user_id' => $user->id,
                'full_name' => $data['full_name'],
                'date_of_birth' => $data['date_of_birth'],
                'phone_number' => $data['phone_number'],
            ]);

            // Generate tokens
            $accessToken = $user->createToken('access_token')->plainTextToken;
            $refreshToken = $this->generateRefreshToken($user);

            DB::commit();

            return [
                'user' => new UserResource($user->load('profile')),
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Login user and return tokens.
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws \Exception
     */
    public function login(string $email, string $password): array
    {
        // Find user by email
        $user = User::where('email', $email)->first();

        // Verify credentials
        if (!$user || !Hash::check($password, $user->password_hash)) {
            throw new \Exception('Invalid credentials', 401);
        }

        // Check if user is active
        if (!$user->is_active) {
            throw new \Exception('Account is inactive. Please contact support.', 403);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Generate tokens
        $accessToken = $user->createToken('access_token')->plainTextToken;
        $refreshToken = $this->generateRefreshToken($user);

        return [
            'user' => new UserResource($user->load('profile')),
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Refresh access token using refresh token.
     *
     * @param string $refreshTokenString
     * @return array
     * @throws \Exception
     */
    public function refresh(string $refreshTokenString): array
    {
        // Find refresh token
        $refreshToken = RefreshToken::where('token', $refreshTokenString)->first();

        if (!$refreshToken) {
            throw new \Exception('Invalid refresh token', 401);
        }

        // Validate token
        if (!$refreshToken->isValid()) {
            throw new \Exception('Refresh token has expired or been revoked', 401);
        }

        $user = $refreshToken->user;

        // Check if user is active
        if (!$user->is_active) {
            throw new \Exception('Account is inactive', 403);
        }

        // Generate new access token (keep refresh token)
        $accessToken = $user->createToken('access_token')->plainTextToken;

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Logout user and revoke tokens.
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        // Revoke all access tokens
        $user->tokens()->delete();

        // Revoke all refresh tokens
        $user->refreshTokens()->update(['is_revoked' => true]);
    }

    /**
     * Generate a refresh token for the user.
     *
     * @param User $user
     * @return string
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
