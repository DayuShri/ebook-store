<?php

namespace App\Services\Frontend;

/**
 * Library Service - Mock Data Layer
 * 
 * This service provides mock library (purchased books) functionality.
 */
class LibraryService
{
    private const SESSION_KEY = 'user_library';
    private CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    /**
     * Get all library items for current user
     */
    public function getLibraryItems(): array
    {
        $library = $this->getLibrary();
        $items = [];

        foreach ($library['items'] as $item) {
            // Only show books with active library status
            $libraryStatus = $item['library_status'] ?? 'active';
            if ($libraryStatus !== 'active') {
                continue;
            }
            
            $book = $this->catalogService->getBook($item['book_id']);
            if ($book) {
                $items[] = [
                    'id' => $item['id'],
                    'book_id' => $item['book_id'],
                    'book' => $book,
                    'order_id' => $item['order_id'],
                    'library_status' => $libraryStatus,
                    'granted_at' => $item['granted_at'],
                    'reading_progress' => $item['reading_progress'] ?? 0,
                    'last_read_at' => $item['last_read_at'] ?? null,
                ];
            }
        }

        return $items;
    }

    /**
     * Check if user has access to a book
     */
    public function hasAccess(string $bookId): bool
    {
        $library = $this->getLibrary();
        
        foreach ($library['items'] as $item) {
            if ($item['book_id'] === $bookId) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get reading progress for a book
     */
    public function getReadingProgress(string $bookId): array
    {
        $library = $this->getLibrary();
        
        foreach ($library['items'] as $item) {
            if ($item['book_id'] === $bookId) {
                return [
                    'last_page_read' => $item['last_page_read'] ?? 1,
                    'total_pages' => $item['total_pages'] ?? 100,
                    'progress_percentage' => $item['reading_progress'] ?? 0,
                    'last_read_at' => $item['last_read_at'] ?? null,
                ];
            }
        }
        
        return [
            'last_page_read' => 1,
            'total_pages' => 100,
            'progress_percentage' => 0,
            'last_read_at' => null,
        ];
    }

    /**
     * Update reading progress
     */
    public function updateReadingProgress(string $bookId, int $currentPage, int $totalPages): array
    {
        $library = $this->getLibrary();
        
        foreach ($library['items'] as &$item) {
            if ($item['book_id'] === $bookId) {
                $item['last_page_read'] = $currentPage;
                $item['total_pages'] = $totalPages;
                $item['reading_progress'] = round(($currentPage / $totalPages) * 100, 1);
                $item['last_read_at'] = now()->toDateTimeString();
                
                session([self::SESSION_KEY => $library]);
                
                return [
                    'success' => true,
                    'progress' => $item['reading_progress'],
                ];
            }
        }
        
        return ['success' => false, 'message' => 'Buku tidak ditemukan di perpustakaan'];
    }

    /**
     * Add book to library (after purchase)
     */
    public function addToLibrary(string $bookId, string $orderId): array
    {
        $library = $this->getLibrary();
        
        // Check if already in library
        foreach ($library['items'] as $item) {
            if ($item['book_id'] === $bookId) {
                return ['success' => false, 'message' => 'Buku sudah ada di perpustakaan'];
            }
        }

        $book = $this->catalogService->getBook($bookId);
        if (!$book) {
            return ['success' => false, 'message' => 'Buku tidak ditemukan'];
        }

        $library['items'][] = [
            'id' => 'lib-' . uniqid(),
            'book_id' => $bookId,
            'order_id' => $orderId,
            'granted_at' => now()->toDateTimeString(),
            'last_page_read' => 1,
            'total_pages' => $book['page_count'] ?? 100,
            'reading_progress' => 0,
            'last_read_at' => null,
        ];

        session([self::SESSION_KEY => $library]);

        return ['success' => true, 'message' => 'Buku ditambahkan ke perpustakaan'];
    }

    /**
     * Get library from session
     */
    private function getLibrary(): array
    {
        return session(self::SESSION_KEY, [
            'items' => $this->getMockLibraryItems(),
        ]);
    }

    /**
     * Mock library items (pre-purchased books)
     */
    private function getMockLibraryItems(): array
    {
        return [
            [
                'id' => 'lib-mock-001',
                'book_id' => 'book-001', // The Clean Coder (PDF)
                'order_id' => 'ORD-TEST001',
                'library_status' => 'active',
                'granted_at' => now()->subDays(3)->toDateTimeString(),
                'last_page_read' => 120,
                'total_pages' => 250,
                'reading_progress' => 48.0,
                'last_read_at' => now()->subMinutes(3)->toDateTimeString(),
            ],
            [
                'id' => 'lib-mock-002',
                'book_id' => 'book-002', // Atomic Habits (EPUB)
                'order_id' => 'ORD-TEST002',
                'library_status' => 'active',
                'granted_at' => now()->subDays(5)->toDateTimeString(),
                'last_page_read' => 1,
                'total_pages' => 320,
                'reading_progress' => 0,
                'last_read_at' => now()->subMinutes(10)->toDateTimeString(),
            ],
            [
                'id' => 'lib-mock-003',
                'book_id' => 'book-003', // Deep Work (EPUB)
                'order_id' => 'ORD-TEST003',
                'library_status' => 'active',
                'granted_at' => now()->subDays(14)->toDateTimeString(),
                'last_page_read' => 1,
                'total_pages' => 300,
                'reading_progress' => 0,
                'last_read_at' => now()->subMinutes(14)->toDateTimeString(),
            ],
        ];
    }
}
