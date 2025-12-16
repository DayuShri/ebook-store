<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    /**
     * Get all users with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            
            // Validate per_page parameter
            if ($perPage < 1 || $perPage > 100) {
                return response()->json([
                    'message' => 'Invalid pagination parameter',
                    'error' => 'per_page must be between 1 and 100',
                ], 400);
            }
            
            $users = User::with('profile')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'data' => UserResource::collection($users->items()),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve users list', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific user by ID.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with('profile')->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Activate a user account.
     */
    public function activate(string $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                ], 404);
            }
            
            if ($user->is_active) {
                return response()->json([
                    'message' => 'User is already active',
                ], 400);
            }

            $user->update(['is_active' => true]);

            return response()->json([
                'message' => 'User activated successfully',
                'data' => new UserResource($user->fresh()),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to activate user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Failed to activate user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deactivate a user account.
     */
    public function deactivate(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        // Prevent self-deactivation
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Cannot deactivate your own account',
            ], 400);
        }

        $user->update(['is_active' => false]);

        // Revoke all tokens
        $user->tokens()->delete();
        $user->refreshTokens()->update(['is_revoked' => true]);

        return response()->json([
            'message' => 'User deactivated successfully',
            'data' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Promote a user to admin.
     */
    public function promoteToAdmin(string $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                ], 404);
            }

            if ($user->role === 'admin') {
                return response()->json([
                    'message' => 'User is already an admin',
                ], 400);
            }

            $user->update(['role' => 'admin']);

            return response()->json([
                'message' => 'User promoted to admin successfully',
                'data' => new UserResource($user->fresh()),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to promote user to admin', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Failed to promote user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Demote an admin to regular user.
     */
    public function demoteToUser(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        // Prevent self-demotion
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Cannot demote yourself',
            ], 400);
        }

        if ($user->role === 'user') {
            return response()->json([
                'message' => 'User is already a regular user',
            ], 400);
        }

        $user->update(['role' => 'user']);

        return response()->json([
            'message' => 'Admin demoted to user successfully',
            'data' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Delete a user account (soft delete by deactivation).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Cannot delete your own account',
            ], 400);
        }

        // Soft delete by deactivating
        $user->update(['is_active' => false]);
        $user->tokens()->delete();
        $user->refreshTokens()->update(['is_revoked' => true]);

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Get user statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'inactive_users' => User::where('is_active', false)->count(),
                'admin_users' => User::where('role', 'admin')->count(),
                'regular_users' => User::where('role', 'user')->count(),
                'recent_registrations' => User::where('created_at', '>=', now()->subDays(7))->count(),
            ];

            return response()->json([
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve user statistics', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
