<?php

namespace App\Modules\Library\Services;

use App\Modules\Review_Reading\Contracts\LibraryAccessService;
use App\Modules\Library\Models\LibraryItem;
use App\Modules\Library\Models\Book;

class LibraryAccessServiceImpl implements LibraryAccessService
{
    public function userHasBook(string $userId, string $bookId): bool
    {
        return LibraryItem::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->where('status', 'ACTIVE')
            ->whereNull('revoked_at')
            ->exists();
    }

    public function getBookDetails(string $bookId): array
    {
        $book = Book::with(['authors', 'publisher'])->find($bookId);
        
        if (!$book) {
            return [
                'id' => $bookId,
                'title' => 'Unknown Book',
                'author' => 'Unknown Author',
                'cover_url' => null
            ];
        }

        return [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->authors->pluck('name')->join(', ') ?: 'Unknown Author',
            'cover_url' => $book->cover_url
        ];
    }
}
