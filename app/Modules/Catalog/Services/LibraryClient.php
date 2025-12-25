<?php

namespace App\Modules\Catalog\Services;

use Illuminate\Support\Facades\Http;

class LibraryClient
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('LIBRARY_SERVICE_URL', 'http://localhost:8000');
    }

    public function getBookFiles(string $bookId, ?string $token = null): array
    {
        $request = Http::baseUrl($this->baseUrl);

        if ($token) {
            $request = $request->withToken($token);
        }

        $response = $request->get(
            "/api/v1/library/files/{$bookId}/PDF",
            ['with_link' => false]
        );

        if (! $response->ok()) {
            return [];
        }

        return $response->json('data') ?? [];
    }
}
