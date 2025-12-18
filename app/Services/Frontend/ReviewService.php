<?php

namespace App\Services\Frontend;

/**
 * Review Service - Mock Data Layer
 * 
 * This service provides mock review functionality.
 */
class ReviewService
{
    /**
     * Get reviews for a book
     */
    public function getBookReviews(string $bookId, int $limit = 10): array
    {
        $allReviews = $this->getMockReviews();
        
        $reviews = array_filter($allReviews, fn($r) => $r['book_id'] === $bookId);
        $reviews = array_slice(array_values($reviews), 0, $limit);
        
        return [
            'reviews' => $reviews,
            'total' => count($reviews),
            'avg_rating' => $this->calculateAvgRating($reviews),
            'rating_distribution' => $this->getRatingDistribution($reviews),
        ];
    }

    /**
     * Create a new review
     */
    public function createReview(string $bookId, int $rating, ?string $text = null): array
    {
        // In real implementation, this would save to database
        return [
            'success' => true,
            'message' => 'Review berhasil ditambahkan',
            'review' => [
                'id' => 'rev-' . uniqid(),
                'user_id' => auth()->id() ?? 'user-mock',
                'user_name' => auth()->user()->profile->full_name ?? 'Pengguna',
                'book_id' => $bookId,
                'rating' => $rating,
                'review_text' => $text,
                'is_verified_purchase' => true,
                'helpful_count' => 0,
                'created_at' => now()->toDateTimeString(),
            ],
        ];
    }

    /**
     * Mark review as helpful/unhelpful
     */
    public function markHelpful(string $reviewId, bool $isHelpful): array
    {
        return [
            'success' => true,
            'message' => $isHelpful ? 'Ditandai sebagai membantu' : 'Ditandai sebagai tidak membantu',
        ];
    }

    /**
     * Calculate average rating
     */
    private function calculateAvgRating(array $reviews): float
    {
        if (empty($reviews)) {
            return 0;
        }
        
        $total = array_sum(array_column($reviews, 'rating'));
        return round($total / count($reviews), 1);
    }

    /**
     * Get rating distribution (1-5 stars)
     */
    private function getRatingDistribution(array $reviews): array
    {
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        
        foreach ($reviews as $review) {
            $distribution[$review['rating']]++;
        }
        
        return $distribution;
    }

    /**
     * Mock reviews data
     */
    private function getMockReviews(): array
    {
        return [
            // Reviews for Atomic Habits (book-002)
            [
                'id' => 'rev-001',
                'user_id' => 'user-001',
                'user_name' => 'Budi Santoso',
                'book_id' => 'book-002',
                'rating' => 5,
                'review_text' => 'Buku yang sangat mengubah cara pandang saya tentang kebiasaan. Penjelasannya mudah dipahami dan praktis.',
                'is_verified_purchase' => true,
                'helpful_count' => 45,
                'created_at' => now()->subDays(10)->toDateTimeString(),
            ],
            [
                'id' => 'rev-002',
                'user_id' => 'user-002',
                'user_name' => 'Siti Rahayu',
                'book_id' => 'book-002',
                'rating' => 5,
                'review_text' => 'Wajib baca! Setelah menerapkan metode 1% lebih baik setiap hari, hidup saya benar-benar berubah.',
                'is_verified_purchase' => true,
                'helpful_count' => 32,
                'created_at' => now()->subDays(15)->toDateTimeString(),
            ],
            [
                'id' => 'rev-003',
                'user_id' => 'user-003',
                'user_name' => 'Ahmad Fauzi',
                'book_id' => 'book-002',
                'rating' => 4,
                'review_text' => 'Buku yang bagus, tapi ada beberapa bagian yang agak repetitif. Overall tetap recommended.',
                'is_verified_purchase' => true,
                'helpful_count' => 18,
                'created_at' => now()->subDays(20)->toDateTimeString(),
            ],
            // Reviews for Laskar Pelangi (book-001)
            [
                'id' => 'rev-004',
                'user_id' => 'user-004',
                'user_name' => 'Dewi Lestari',
                'book_id' => 'book-001',
                'rating' => 5,
                'review_text' => 'Novel yang sangat menyentuh hati. Membaca kisah perjuangan anak-anak Belitung ini membuat saya lebih mensyukuri pendidikan.',
                'is_verified_purchase' => true,
                'helpful_count' => 89,
                'created_at' => now()->subDays(5)->toDateTimeString(),
            ],
            [
                'id' => 'rev-005',
                'user_id' => 'user-005',
                'user_name' => 'Rizky Pratama',
                'book_id' => 'book-001',
                'rating' => 5,
                'review_text' => 'Klasik Indonesia yang wajib dibaca. Andrea Hirata berhasil menggambarkan semangat juang yang menginspirasi.',
                'is_verified_purchase' => true,
                'helpful_count' => 67,
                'created_at' => now()->subDays(12)->toDateTimeString(),
            ],
            [
                'id' => 'rev-006',
                'user_id' => 'user-006',
                'user_name' => 'Maya Putri',
                'book_id' => 'book-001',
                'rating' => 4,
                'review_text' => 'Ceritanya bagus dan mengharukan, walau kadang terasa sedikit panjang di beberapa bagian.',
                'is_verified_purchase' => false,
                'helpful_count' => 23,
                'created_at' => now()->subDays(25)->toDateTimeString(),
            ],
            // Reviews for Clean Code (book-004)
            [
                'id' => 'rev-007',
                'user_id' => 'user-007',
                'user_name' => 'Andi Wijaya',
                'book_id' => 'book-004',
                'rating' => 5,
                'review_text' => 'Buku wajib untuk setiap programmer! Prinsip-prinsip clean code yang diajarkan sangat berguna dalam pekerjaan sehari-hari.',
                'is_verified_purchase' => true,
                'helpful_count' => 56,
                'created_at' => now()->subDays(8)->toDateTimeString(),
            ],
            [
                'id' => 'rev-008',
                'user_id' => 'user-008',
                'user_name' => 'Linda Kusuma',
                'book_id' => 'book-004',
                'rating' => 4,
                'review_text' => 'Sangat bagus untuk meningkatkan kualitas kode. Contohnya kebanyakan Java, tapi prinsipnya universal.',
                'is_verified_purchase' => true,
                'helpful_count' => 34,
                'created_at' => now()->subDays(18)->toDateTimeString(),
            ],
            // Reviews for Filosofi Teras (book-006)
            [
                'id' => 'rev-009',
                'user_id' => 'user-009',
                'user_name' => 'Farhan Akbar',
                'book_id' => 'book-006',
                'rating' => 5,
                'review_text' => 'Filsafat Stoa dikemas dengan sangat ringan dan relevan dengan kehidupan modern. Recommended banget!',
                'is_verified_purchase' => true,
                'helpful_count' => 78,
                'created_at' => now()->subDays(7)->toDateTimeString(),
            ],
            [
                'id' => 'rev-010',
                'user_id' => 'user-010',
                'user_name' => 'Putri Handayani',
                'book_id' => 'book-006',
                'rating' => 5,
                'review_text' => 'Buku ini membantu saya menghadapi kecemasan dengan lebih baik. Penulisnya berhasil membuat filsafat jadi mudah dipahami.',
                'is_verified_purchase' => true,
                'helpful_count' => 62,
                'created_at' => now()->subDays(14)->toDateTimeString(),
            ],
        ];
    }
}
