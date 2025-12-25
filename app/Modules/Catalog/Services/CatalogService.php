<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Book;
use Illuminate\Support\Str;

use App\Modules\Catalog\Services\LibraryClient;


class CatalogService
{
    /**
     * Create new book with categories
     */
    public function createBook(array $data): Book
    {
        $data['id'] = $data['id'] ?? (string) Str::uuid();

        $book = Book::create($data);

        if (!empty($data['category_ids'])) {
            $book->categories()->sync($data['category_ids']);
        }

        return $book->load('categories');
    }

    /**
     * Update book & sync categories
     */
    public function updateBook(Book $book, array $data): Book
    {
        $book->update($data);

        if (array_key_exists('category_ids', $data)) {
            $book->categories()->sync($data['category_ids'] ?? []);
        }

        return $book->load('categories');
    }

    /**
     * Delete book
     */
    public function deleteBook(Book $book): void
    {
        $book->categories()->detach();
        $book->delete();
    }

    /**
     * Public list books
     */
    public function listBooks()
    {
        return Book::with('categories')
            ->where('is_active', true)
            ->paginate(10);
    }

    /**
     * Public book detail with review stats
     */
    public function getBookDetail(string $id): array
    {
        $book = Book::with('categories')->findOrFail($id);
        
        // Get review statistics
        $reviewStats = $this->reviewService->getReviewStats($id);
        
        return [
            'id' => $book->id,
            'title' => $book->title,
            'subtitle' => $book->subtitle,
            'author' => $book->author,
            'publisher' => $book->publisher,
            'synopsis' => $book->synopsis,
            'price' => $book->price,
            'discount_percentage' => $book->discount_percentage,
            'cover_image_url' => $book->cover_image_url,
            'publication_date' => $book->publication_date,
            'page_count' => $book->page_count,
            'is_active' => $book->is_active,
            'categories' => $book->categories,
            'avg_rating' => $reviewStats['average'],
            'review_count' => $reviewStats['count'],
            'review_distribution' => $reviewStats['distribution'],
        ];
    }

    public function getPublicBooks(array $filters = [])
    {
        $query = Book::query()
            ->where('is_active', true)
            ->with('categories');

        // optional filter
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        $books = $query->paginate(10);
        
        // Enrich each book with rating
        $books->getCollection()->transform(function ($book) {
            return $this->enrichBookWithRating($book);
        });
        
        return $books;
    }
    
    /**
     * Enrich book object with avg_rating
     */
    protected function enrichBookWithRating($book)
    {
        $book->avg_rating = $this->reviewService->getAverageRating($book->id);
        return $book;
    }



    
    protected LibraryClient $library;
    protected $reviewService;

    public function __construct(LibraryClient $library)
    {
        $this->library = $library;
        // Inject ReviewService for rating data
        $this->reviewService = app(\App\Modules\Review_Reading\Services\ReviewService::class);
    }

    public function detail(string $id, ?string $token = null)
    {
        $book = Book::with('categories')->find($id);
        if (! $book) {
            return null;
        }

        $files = $this->library->getBookFiles($id, $token);

        return [
            'id' => $book->id,
            'title' => $book->title,
            'price' => $book->price,
            'categories' => $book->categories,
            'files' => $files
        ];
    }

}
