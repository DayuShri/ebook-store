<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()->load('profile')),
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get or create user profile
            $profile = $user->profile;

            if (!$profile) {
                $profile = UserProfile::create([
                    'user_id' => $user->id,
                    'full_name' => $request->full_name,
                    'profile_picture_url' => $request->profile_picture_url,
                    'date_of_birth' => $request->date_of_birth,
                    'phone_number' => $request->phone_number,
                ]);
            } else {
                $profile->update($request->only([
                    'full_name',
                    'profile_picture_url',
                    'date_of_birth',
                    'phone_number',
                ]));
            }

            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => new UserProfileResource($profile->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Profile update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the authenticated user's profile.
     */
    public function getProfile(Request $request): JsonResponse
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found',
            ], 404);
        }

        return response()->json([
            'data' => new UserProfileResource($profile),
        ]);
    }

    /**
     * Deactivate the authenticated user's account.
     */
    public function deactivate(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $user->update(['is_active' => false]);

            // Revoke all tokens
            $user->tokens()->delete();
            $user->refreshTokens()->update(['is_revoked' => true]);

            return response()->json([
                'message' => 'Account deactivated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Account deactivation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
