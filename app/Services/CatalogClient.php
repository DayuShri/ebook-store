<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CatalogClient
{
    public function bulk(array $bookIds): array
    {
        $url = config('services.catalog.base_url') . '/api/v1/catalog/books/bulk';

        $resp = Http::acceptJson()
            ->timeout(5)
            ->post($url, ['book_ids' => $bookIds]);

        if ($resp->failed()) {
            abort(503, 'Catalog service unavailable');
        }

        return $resp->json('items') ?? [];
    }
}
