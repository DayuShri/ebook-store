<?php

namespace App\Modules\Library\Services;

use App\Modules\Review_Reading\Contracts\LibraryAccessService;
use App\Modules\Library\Models\LibraryItem;
use App\Modules\Catalog\Models\Book;

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
                'title' => 'Book Not Found',
                'authors' => [],
                'cover_image_url' => null
            ];
        }

        return [
            'id' => $book->id,
            'title' => $book->title,
            'authors' => $book->authors->map(function($author) {
                return ['name' => $author->name];
            })->toArray(),
            'cover_image_url' => $book->cover_url
        ];
    }
}
