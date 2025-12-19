<?php

namespace App\Modules\Review_Reading\Contracts;

interface LibraryAccessService
{
    public function userHasBook(string $userId, string $bookId): bool;
    
    public function getBookDetails(string $bookId): array;
}
