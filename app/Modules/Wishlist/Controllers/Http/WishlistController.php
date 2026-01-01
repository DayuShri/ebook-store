<?php

namespace App\Modules\Wishlist\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Wishlist\Exceptions\BookAlreadyInWishlistException;
use App\Modules\Wishlist\Exceptions\BookNotFoundException;
use App\Modules\Wishlist\Exceptions\BookNotInWishlistException;
use App\Modules\Wishlist\Exceptions\WishlistException;
use App\Modules\Wishlist\Requests\AddToWishlistRequest;
use App\Modules\Wishlist\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WishlistController extends Controller
{
    protected WishlistService $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    /**
     * Get authenticated user's wishlist.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $wishlist = $this->wishlistService->getUserWishlist($request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Wishlist retrieved successfully',
                'data' => $wishlist,
            ]);
        } catch (WishlistException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error in wishlist index', [
                'user_id' => $request->user()->id,
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
     * Add a book to wishlist.
     *
     * @param AddToWishlistRequest $request
     * @return JsonResponse
     */
    public function store(AddToWishlistRequest $request): JsonResponse
    {
        try {
            $wishlistItem = $this->wishlistService->addToWishlist(
                $request->user()->id,
                $request->book_id
            );

            // Get book details via HMVC Catalog integration
            $bookData = app(\App\Services\CatalogClient::class)->getBookFullDetail($request->book_id);

            return response()->json([
                'success' => true,
                'message' => 'Book added to wishlist successfully',
                'data' => [
                    'id' => $wishlistItem->id,
                    'user_id' => $wishlistItem->user_id,
                    'book_id' => $wishlistItem->book_id,
                    'added_at' => $wishlistItem->added_at,
                    'book' => $bookData,
                ],
            ], 201);
        } catch (BookNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (BookAlreadyInWishlistException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (WishlistException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error adding to wishlist', [
                'user_id' => $request->user()->id,
                'book_id' => $request->book_id,
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
     * Remove a book from wishlist.
     *
     * @param Request $request
     * @param string $bookId
     * @return JsonResponse
     */
    public function destroy(Request $request, string $bookId): JsonResponse
    {
        try {
            $this->wishlistService->removeFromWishlist(
                $request->user()->id,
                $bookId
            );

            return response()->json([
                'success' => true,
                'message' => 'Book removed from wishlist successfully',
            ]);
        } catch (BookNotInWishlistException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (WishlistException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error removing from wishlist', [
                'user_id' => $request->user()->id,
                'book_id' => $bookId,
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
     * Check if a book is in wishlist.
     *
     * @param Request $request
     * @param string $bookId
     * @return JsonResponse
     */
    public function check(Request $request, string $bookId): JsonResponse
    {
        try {
            $inWishlist = $this->wishlistService->isInWishlist(
                $request->user()->id,
                $bookId
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'in_wishlist' => $inWishlist,
                ],
            ]);
        } catch (WishlistException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error checking wishlist', [
                'user_id' => $request->user()->id,
                'book_id' => $bookId,
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
     * Get wishlist count.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function count(Request $request): JsonResponse
    {
        try {
            $count = $this->wishlistService->getWishlistCount($request->user()->id);

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
            Log::error('Unexpected error getting wishlist count', [
                'user_id' => $request->user()->id,
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
     * Clear all items from wishlist.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            $deletedCount = $this->wishlistService->clearWishlist($request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Wishlist cleared successfully',
                'data' => [
                    'deleted_count' => $deletedCount,
                ],
            ]);
        } catch (WishlistException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error clearing wishlist', [
                'user_id' => $request->user()->id,
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
