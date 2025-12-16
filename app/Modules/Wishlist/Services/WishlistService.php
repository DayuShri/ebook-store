<?php

namespace App\Modules\Wishlist\Services;

use App\Modules\Wishlist\Exceptions\BookAlreadyInWishlistException;
use App\Modules\Wishlist\Exceptions\BookNotFoundException;
use App\Modules\Wishlist\Exceptions\BookNotInWishlistException;
use App\Modules\Wishlist\Exceptions\WishlistException;
use App\Modules\Wishlist\Models\Book;
use App\Modules\Wishlist\Models\Wishlist;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WishlistService
{
    /**
     * Get user's wishlist with book details.
     *
     * @param string $userId
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws WishlistException
     */
    public function getUserWishlist(string $userId)
    {
        try {
            return Wishlist::where('user_id', $userId)
                ->with([
                    'book' => function ($query) {
                        $query->with(['authors', 'publisher', 'categories']);
                    }
                ])
                ->orderBy('added_at', 'desc')
                ->get();
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
            // Verify book exists
            $book = Book::find($bookId);
            if (!$book) {
                throw new BookNotFoundException("Book with ID {$bookId} not found");
            }

            // Check if book is active
            if (!$book->is_active) {
                throw new BookNotFoundException("Book is not available");
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
