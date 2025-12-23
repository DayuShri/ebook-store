<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

<<<<<<< HEAD
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
=======
/**
 * HMVC Client for inter-module communication with Catalog module.
 * Per INSTRUCTION.md: Use HMVC API for communication between modules.
 * 
 * NOTE: Uses localhost for internal HMVC calls to avoid self-referential
 * HTTP issues when app.url points to ngrok or external URL.
 */
class CatalogClient
{
    protected string $baseUrl;

    public function __construct()
    {
        // Use localhost for internal HMVC calls (avoid ngrok self-referential issues)
        $internalUrl = config('hmvc.internal_url', 'http://127.0.0.1:8000');
        $this->baseUrl = $internalUrl . '/hmvc/catalog';
    }

    /**
     * Get bulk pricing info for multiple books.
     *
     * @param array $bookIds Array of book UUIDs
     * @return array List of books with book_id, price, discount_percentage, is_active
     */
    public function bulk(array $bookIds): array
    {
        $response = Http::post($this->baseUrl . '/books/bulk-price', [
            'book_ids' => $bookIds,
        ]);

        if ($response->failed()) {
            \Log::error('CatalogClient bulk failed', [
                'book_ids' => $bookIds,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        }

        return $response->json('data') ?? [];
    }

    /**
     * Get single book price info.
     *
     * @param string $bookId
     * @return array|null
     */
    public function price(string $bookId): ?array
    {
        $response = Http::get($this->baseUrl . "/books/{$bookId}/price");

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Check if a book exists.
     *
     * @param string $bookId
     * @return bool
     */
    public function exists(string $bookId): bool
    {
        $response = Http::get($this->baseUrl . "/books/{$bookId}/exists");

        if ($response->failed()) {
            return false;
        }

        return $response->json('exists') ?? false;
>>>>>>> 2347f10c6476bdca24e206f24d6746d0805d9124
    }
}
