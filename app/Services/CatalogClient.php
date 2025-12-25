<?php

namespace App\Services;

use App\Modules\Catalog\Services\BookService;

class CatalogClient
{
    protected $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    public function bulk(array $bookIds)
    {
        return $this->bookService->bulkPrice($bookIds);
    }
}
