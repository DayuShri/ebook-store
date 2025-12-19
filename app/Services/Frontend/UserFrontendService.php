<?php

namespace App\Services\Frontend;

/**
 * User Frontend Service - Mock Data Layer
 * 
 * This service provides mock user profile and wishlist functionality.
 */
class UserFrontendService
{
    private const WISHLIST_KEY = 'user_wishlist';
    private CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    /**
     * Get user profile
     */
    public function getProfile(): array
    {
        // In real implementation, this would fetch from API
        $user = auth()->user();
        
        if (!$user) {
            return $this->getMockProfile();
        }

        return [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role ?? 'user',
            'full_name' => $user->profile?->full_name ?? 'Pengguna',
            'phone_number' => $user->profile?->phone_number ?? null,
            'date_of_birth' => $user->profile?->date_of_birth ?? null,
            'profile_picture_url' => $user->profile?->profile_picture_url ?? null,
            'created_at' => $user->created_at?->toDateTimeString(),
        ];
    }

    /**
     * Update user profile
     */
    public function updateProfile(array $data): array
    {
        // In real implementation, this would call API
        // For now, just return success
        return [
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'profile' => array_merge($this->getProfile(), $data),
        ];
    }

    /**
     * Get wishlist items
     */
    public function getWishlist(): array
    {
        $wishlist = session(self::WISHLIST_KEY, $this->getMockWishlist());
        $items = [];

        foreach ($wishlist as $bookId) {
            $book = $this->catalogService->getBook($bookId);
            if ($book) {
                $items[] = $book;
            }
        }

        return $items;
    }

    /**
     * Add book to wishlist
     */
    public function addToWishlist(string $bookId): array
    {
        $wishlist = session(self::WISHLIST_KEY, $this->getMockWishlist());
        
        if (in_array($bookId, $wishlist)) {
            return ['success' => false, 'message' => 'Buku sudah ada di wishlist'];
        }

        $book = $this->catalogService->getBook($bookId);
        if (!$book) {
            return ['success' => false, 'message' => 'Buku tidak ditemukan'];
        }

        $wishlist[] = $bookId;
        session([self::WISHLIST_KEY => $wishlist]);

        return ['success' => true, 'message' => 'Buku ditambahkan ke wishlist'];
    }

    /**
     * Remove book from wishlist
     */
    public function removeFromWishlist(string $bookId): array
    {
        $wishlist = session(self::WISHLIST_KEY, $this->getMockWishlist());
        
        $wishlist = array_values(array_filter($wishlist, fn($id) => $id !== $bookId));
        session([self::WISHLIST_KEY => $wishlist]);

        return ['success' => true, 'message' => 'Buku dihapus dari wishlist'];
    }

    /**
     * Check if book is in wishlist
     */
    public function isInWishlist(string $bookId): bool
    {
        $wishlist = session(self::WISHLIST_KEY, $this->getMockWishlist());
        return in_array($bookId, $wishlist);
    }

    /**
     * Get wishlist count
     */
    public function getWishlistCount(): int
    {
        $wishlist = session(self::WISHLIST_KEY, $this->getMockWishlist());
        return count($wishlist);
    }

    /**
     * Mock profile data
     */
    private function getMockProfile(): array
    {
        return [
            'id' => 'user-mock-001',
            'email' => 'user@example.com',
            'role' => 'user',
            'full_name' => 'Pengguna Demo',
            'phone_number' => '+62812345678',
            'date_of_birth' => '1990-05-15',
            'profile_picture_url' => null,
            'created_at' => now()->subMonths(6)->toDateTimeString(),
        ];
    }

    /**
     * Mock wishlist data
     */
    private function getMockWishlist(): array
    {
        return [
            'book-001', // Laskar Pelangi
            'book-005', // Rich Dad Poor Dad
            'book-008', // Machine Learning dengan Python
        ];
    }
}
