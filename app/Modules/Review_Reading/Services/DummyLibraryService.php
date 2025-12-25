<?php

namespace App\Modules\Review_Reading\Services;

use App\Modules\Review_Reading\Contracts\LibraryAccessService;

class DummyLibraryService implements LibraryAccessService
{
    public function userHasBook(string $userId, string $bookId): bool
    {
        // DUMMY: anggap semua user punya semua buku
        return true;
    }
}
