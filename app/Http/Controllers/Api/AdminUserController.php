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
        $perPage = $request->get('per_page', 15);
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
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $user->update(['is_active' => true]);

        return response()->json([
            'message' => 'User activated successfully',
            'data' => new UserResource($user->fresh()),
        ]);
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
    }
}
