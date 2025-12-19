<?php

namespace App\Services\Frontend;

use App\Modules\Wishlist\Services\WishlistService;
use App\Modules\Wishlist\Exceptions\BookAlreadyInWishlistException;
use App\Modules\Wishlist\Exceptions\BookNotFoundException;
use App\Modules\Wishlist\Exceptions\BookNotInWishlistException;
use App\Modules\Wishlist\Exceptions\WishlistException;
use Illuminate\Support\Facades\Log;

/**
 * User Frontend Service
 * 
 * This service provides user profile and wishlist functionality.
 * Wishlist operations are integrated with the Wishlist HMVC module.
 */
class UserFrontendService
{
    private WishlistService $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    /**
     * Get user profile
     */
    public function getProfile(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return $this->getMockProfile();
        }

        // Format date_of_birth as Y-m-d string for HTML date input
        $dateOfBirth = $user->profile?->date_of_birth;
        if ($dateOfBirth instanceof \Carbon\Carbon) {
            $dateOfBirth = $dateOfBirth->format('Y-m-d');
        }

        return [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role ?? 'user',
            'full_name' => $user->profile?->full_name ?? 'Pengguna',
            'phone_number' => $user->profile?->phone_number ?? null,
            'date_of_birth' => $dateOfBirth,
            'profile_picture_url' => $user->profile?->profile_picture_url ?? null,
            'created_at' => $user->created_at?->toDateTimeString(),
        ];
    }

    /**
     * Update user profile
     */
    public function updateProfile(array $data): array
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ];
            }

            // Get or create user profile
            $profile = $user->profile;

            if (!$profile) {
                $profile = \App\Models\UserProfile::create([
                    'user_id' => $user->id,
                    'full_name' => $data['full_name'] ?? 'Pengguna',
                    'phone_number' => $data['phone_number'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                ]);
            } else {
                $profile->update([
                    'full_name' => $data['full_name'] ?? $profile->full_name,
                    'phone_number' => $data['phone_number'] ?? $profile->phone_number,
                    'date_of_birth' => $data['date_of_birth'] ?? $profile->date_of_birth,
                ]);
            }

            return [
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'profile' => $this->getProfile(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memperbarui profil: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get wishlist items
     */
    public function getWishlist(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return [];
        }

        try {
            return $this->wishlistService->getUserWishlist($user->id);
        } catch (WishlistException $e) {
            Log::error('Failed to get wishlist', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Add book to wishlist
     */
    public function addToWishlist(string $bookId): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Silakan login terlebih dahulu'];
        }

        try {
            $this->wishlistService->addToWishlist($user->id, $bookId);
            return ['success' => true, 'message' => 'Buku ditambahkan ke wishlist'];
        } catch (BookAlreadyInWishlistException $e) {
            return ['success' => false, 'message' => 'Buku sudah ada di wishlist'];
        } catch (BookNotFoundException $e) {
            return ['success' => false, 'message' => 'Buku tidak ditemukan'];
        } catch (WishlistException $e) {
            Log::error('Failed to add to wishlist', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal menambahkan ke wishlist'];
        }
    }

    /**
     * Remove book from wishlist
     */
    public function removeFromWishlist(string $bookId): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Silakan login terlebih dahulu'];
        }

        try {
            $this->wishlistService->removeFromWishlist($user->id, $bookId);
            return ['success' => true, 'message' => 'Buku dihapus dari wishlist'];
        } catch (BookNotInWishlistException $e) {
            return ['success' => false, 'message' => 'Buku tidak ada di wishlist'];
        } catch (WishlistException $e) {
            Log::error('Failed to remove from wishlist', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal menghapus dari wishlist'];
        }
    }

    /**
     * Check if book is in wishlist
     */
    public function isInWishlist(string $bookId): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        try {
            return $this->wishlistService->isInWishlist($user->id, $bookId);
        } catch (WishlistException $e) {
            Log::error('Failed to check wishlist', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get wishlist count
     */
    public function getWishlistCount(): int
    {
        $user = auth()->user();
        
        if (!$user) {
            return 0;
        }

        try {
            return $this->wishlistService->getWishlistCount($user->id);
        } catch (WishlistException $e) {
            Log::error('Failed to get wishlist count', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Mock profile data (for unauthenticated users)
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
}
