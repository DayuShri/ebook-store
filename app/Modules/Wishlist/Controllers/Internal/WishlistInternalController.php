<?php

namespace App\Modules\Wishlist\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Modules\Wishlist\Exceptions\WishlistException;
use App\Modules\Wishlist\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WishlistInternalController extends Controller
{
    protected WishlistService $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    /**
     * Check if a book is in user's wishlist (HMVC).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function check(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|uuid|exists:users,id',
                'book_id' => 'required|uuid|exists:books,id',
            ]);

            $inWishlist = $this->wishlistService->isInWishlist(
                $validated['user_id'],
                $validated['book_id']
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'in_wishlist' => $inWishlist,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (WishlistException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('HMVC: Unexpected error checking wishlist', [
                'request_data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 500);
        }
    }

    /**
     * Get wishlist count for a user (HMVC).
     *
     * @param string $userId
     * @return JsonResponse
     */
    public function count(string $userId): JsonResponse
    {
        try {
            $count = $this->wishlistService->getWishlistCount($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                ],
            ]);
        } catch (WishlistException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('HMVC: Unexpected error getting wishlist count', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 500);
        }
    }

    /**
     * Get user's wishlist (HMVC).
     *
     * @param string $userId
     * @return JsonResponse
     */
    public function getUserWishlist(string $userId): JsonResponse
    {
        try {
            $wishlist = $this->wishlistService->getUserWishlist($userId);

            return response()->json([
                'success' => true,
                'data' => $wishlist,
            ]);
        } catch (WishlistException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('HMVC: Unexpected error retrieving wishlist', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 500);
        }
    }
}
