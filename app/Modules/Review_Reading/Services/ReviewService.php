<?php

namespace App\Modules\Review_Reading\Services;

use App\Modules\Review_Reading\Models\Review;
use App\Modules\Review_Reading\Models\ReviewHelpfulness;
use App\Modules\Review_Reading\Contracts\LibraryAccessService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReviewService
{
    public function __construct(
        protected LibraryAccessService $library
    ) {}

    public function store(string $userId, array $data): void
    {
        // Dummy access check (nanti otomatis ke real library)
        if (!$this->library->userHasBook($userId, $data['book_id'])) {
            abort(403, 'You do not own this book');
        }

        Review::updateOrCreate(
            [
                'user_id' => $userId,
                'book_id' => $data['book_id']
            ],
            [
                'id' => Str::uuid(),
                'rating' => $data['rating'],
                'review_text' => $data['review_text'] ?? null,
                'is_verified_purchase' => true // DUMMY
            ]
        );
    }

    public function helpful(string $userId, string $reviewId, bool $isHelpful): void
    {
        DB::transaction(function () use ($userId, $reviewId, $isHelpful) {

            $helpful = ReviewHelpfulness::updateOrCreate(
                [
                    'review_id' => $reviewId,
                    'user_id' => $userId
                ],
                [
                    'id' => Str::uuid(),
                    'is_helpful' => $isHelpful
                ]
            );

            // Sinkronkan helpful_count
            $count = ReviewHelpfulness::where('review_id', $reviewId)
                ->where('is_helpful', true)
                ->count();

            Review::where('id', $reviewId)
                ->update(['helpful_count' => $count]);
        });
    }

    public function getByBook(string $bookId)
    {
        return Review::with('user:id,email')
            ->where('book_id', $bookId)
            ->orderByDesc('helpful_count')
            ->orderByDesc('created_at')
            ->get([
                'id',
                'user_id',
                'rating',
                'review_text',
                'helpful_count',
                'created_at'
            ]);
    }

    public function update(string $reviewId, string $userId, array $data): void
    {
        $review = Review::where('id', $reviewId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $review->update($data);
    }

    public function delete(string $reviewId, string $userId): void
    {
        $review = Review::where('id', $reviewId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $review->delete();
    }

    public function getUserReviews(string $userId)
    {
        return Review::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($review) {
                // Get book details from Library module
                $book = $this->library->getBookDetails($review->book_id);
                
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'review_text' => $review->review_text,
                    'created_at' => $review->created_at,
                    'book' => [
                        'id' => $book['id'] ?? $review->book_id,
                        'title' => $book['title'] ?? 'Unknown Book',
                        'author' => $book['author'] ?? 'Unknown Author',
                        'cover_url' => $book['cover_url'] ?? null
                    ]
                ];
            });
    }

    /**
     * Get average rating for a book
     */
    public function getAverageRating(string $bookId): ?float
    {
        $avg = Review::where('book_id', $bookId)->avg('rating');
        return $avg ? round($avg, 1) : null;
    }

    /**
     * Get review statistics for a book
     */
    public function getReviewStats(string $bookId): array
    {
        $reviews = Review::where('book_id', $bookId)->get();
        
        if ($reviews->isEmpty()) {
            return [
                'count' => 0,
                'average' => null,
                'distribution' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]
            ];
        }

        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[$i] = $reviews->where('rating', $i)->count();
        }

        return [
            'count' => $reviews->count(),
            'average' => round($reviews->avg('rating'), 1),
            'distribution' => $distribution
        ];
    }

    /**
     * Get reviews for a book with user info
     */
    public function getBookReviews(string $bookId, ?int $limit = null)
    {
        $query = Review::with(['user.profile'])
            ->where('book_id', $bookId)
            ->orderByDesc('helpful_count')
            ->orderByDesc('created_at');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
