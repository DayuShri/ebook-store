<?php

namespace App\Modules\Wishlist\Services;

use App\Modules\Wishlist\Exceptions\BookAlreadyInWishlistException;
use App\Modules\Wishlist\Exceptions\BookNotFoundException;
use App\Modules\Wishlist\Exceptions\BookNotInWishlistException;
use App\Modules\Wishlist\Exceptions\WishlistException;
use App\Modules\Wishlist\Models\Wishlist;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WishlistService
{
    /**
     * Get the HMVC base URL.
     */
    protected function hmvcUrl(string $path): string
    {
        return config('app.url') . '/hmvc/catalog' . $path;
    }

    /**
     * Get user's wishlist with book details.
     *
     * @param string $userId
     * @return array
     * @throws WishlistException
     */
    public function getUserWishlist(string $userId): array
    {
        try {
            $wishlistItems = Wishlist::where('user_id', $userId)
                ->orderBy('added_at', 'desc')
                ->get();

            $result = [];

            foreach ($wishlistItems as $item) {
                // Call Catalog HMVC to get book details
                $response = Http::get($this->hmvcUrl("/books/{$item->book_id}/full"));

                if ($response->successful()) {
                    $bookData = $response->json();
                    $result[] = [
                        'id' => $item->id,
                        'user_id' => $item->user_id,
                        'book_id' => $item->book_id,
                        'added_at' => $item->added_at,
                        'book' => $bookData,
                    ];
                } else {
                    // Book might have been deleted, still include in wishlist
                    $result[] = [
                        'id' => $item->id,
                        'user_id' => $item->user_id,
                        'book_id' => $item->book_id,
                        'added_at' => $item->added_at,
                        'book' => null,
                    ];
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve wishlist', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw new WishlistException('Failed to retrieve wishlist: ' . $e->getMessage());
        }
    }

    /**
     * Add a book to user's wishlist.
     *
     * @param string $userId
     * @param string $bookId
     * @return Wishlist
     * @throws BookNotFoundException
     * @throws BookAlreadyInWishlistException
     * @throws WishlistException
     */
    public function addToWishlist(string $userId, string $bookId): Wishlist
    {
        try {
            // Verify book exists via Catalog HMVC
            $response = Http::get($this->hmvcUrl("/books/{$bookId}/exists"));

            if (!$response->successful()) {
                throw new BookNotFoundException("Failed to verify book with ID {$bookId}");
            }

            $data = $response->json();
            if (!$data['exists']) {
                throw new BookNotFoundException("Book with ID {$bookId} not found or not active");
            }

            // Check if already in wishlist
            $existing = Wishlist::where('user_id', $userId)
                ->where('book_id', $bookId)
                ->first();

            if ($existing) {
                throw new BookAlreadyInWishlistException();
            }

            return Wishlist::create([
                'user_id' => $userId,
                'book_id' => $bookId,
            ]);
        } catch (BookNotFoundException | BookAlreadyInWishlistException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to add book to wishlist', [
                'user_id' => $userId,
                'book_id' => $bookId,
                'error' => $e->getMessage(),
            ]);
            throw new WishlistException('Failed to add book to wishlist: ' . $e->getMessage());
        }
    }

    /**
     * Remove a book from user's wishlist.
     *
     * @param string $userId
     * @param string $bookId
     * @return bool
     * @throws BookNotInWishlistException
     * @throws WishlistException
     */
    public function removeFromWishlist(string $userId, string $bookId): bool
    {
        try {
            $wishlistItem = Wishlist::where('user_id', $userId)
                ->where('book_id', $bookId)
                ->first();

            if (!$wishlistItem) {
                throw new BookNotInWishlistException();
            }

            return $wishlistItem->delete();
        } catch (BookNotInWishlistException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to remove book from wishlist', [
                'user_id' => $userId,
                'book_id' => $bookId,
                'error' => $e->getMessage(),
            ]);
            throw new WishlistException('Failed to remove book from wishlist: ' . $e->getMessage());
        }
    }

    /**
     * Check if a book is in user's wishlist.
     *
     * @param string $userId
     * @param string $bookId
     * @return bool
     * @throws WishlistException
     */
    public function isInWishlist(string $userId, string $bookId): bool
    {
        try {
            return Wishlist::where('user_id', $userId)
                ->where('book_id', $bookId)
                ->exists();
        } catch (\Exception $e) {
            Log::error('Failed to check wishlist status', [
                'user_id' => $userId,
                'book_id' => $bookId,
                'error' => $e->getMessage(),
            ]);
            throw new WishlistException('Failed to check wishlist status: ' . $e->getMessage());
        }
    }

    /**
     * Get the count of items in user's wishlist.
     *
     * @param string $userId
     * @return int
     * @throws WishlistException
     */
    public function getWishlistCount(string $userId): int
    {
        try {
            return Wishlist::where('user_id', $userId)->count();
        } catch (\Exception $e) {
            Log::error('Failed to get wishlist count', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw new WishlistException('Failed to get wishlist count: ' . $e->getMessage());
        }
    }

    /**
     * Clear all items from user's wishlist.
     *
     * @param string $userId
     * @return int Number of items deleted
     * @throws WishlistException
     */
    public function clearWishlist(string $userId): int
    {
        try {
            return Wishlist::where('user_id', $userId)->delete();
        } catch (\Exception $e) {
            Log::error('Failed to clear wishlist', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw new WishlistException('Failed to clear wishlist: ' . $e->getMessage());
        }
    }
}
