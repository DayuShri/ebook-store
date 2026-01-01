<?php

namespace App\Services;

use App\Modules\Catalog\Services\CatalogService;

class CatalogClient
{
    protected $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function bulk(array $bookIds)
    {
        // Fetch book details for pricing
        $books = [];
        foreach ($bookIds as $bookId) {
            try {
                $book = $this->catalogService->getBookDetail($bookId);
                if ($book) {
                    $books[] = [
                        'book_id' => $book['id'],
                        'title' => $book['title'],
                        'price' => $book['price'],
                        'discount_percentage' => $book['discount_percentage'] ?? 0,
                        'is_active' => $book['is_active'] ?? true,
                    ];
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch book for bulk pricing', [
                    'book_id' => $bookId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        return $books;
    }

    /**
     * Check if a book exists and is active.
     *
     * @param string $bookId
     * @return bool
     */
    public function checkBookExists(string $bookId): bool
    {
        try {
            return $this->catalogService->checkBookExists($bookId);
        } catch (\Exception $e) {
            \Log::warning('Failed to check book existence', [
                'book_id' => $bookId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get full book details with categories.
     *
     * @param string $bookId
     * @return array|null
     */
    public function getBookFullDetail(string $bookId): ?array
    {
        try {
            return $this->catalogService->getBookFullDetail($bookId);
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch full book details', [
                'book_id' => $bookId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
