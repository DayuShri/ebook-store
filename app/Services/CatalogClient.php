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
                        'book_id' => $book->id,
                        'title' => $book->title,
                        'price' => $book->price,
                        'discount_percentage' => $book->discount_percentage ?? 0,
                        'is_active' => $book->is_active ?? true,
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
}
