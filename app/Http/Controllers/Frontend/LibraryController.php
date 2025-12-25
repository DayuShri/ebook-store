<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LibraryController extends Controller
{
    /**
     * Get authentication token for API calls
     */
    protected function getAuthToken(): ?string
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        if ($user->currentAccessToken()) {
            return $user->currentAccessToken()->token;
        }
        
        $sessionToken = session('api_token');
        if ($sessionToken) {
            $tokenExists = \Laravel\Sanctum\PersonalAccessToken::findToken($sessionToken);
            if ($tokenExists && $tokenExists->tokenable_id === $user->id) {
                return $sessionToken;
            }
        }

        try {
            $token = $user->createToken('web-session-' . now()->timestamp)->plainTextToken;
            session(['api_token' => $token]);
            return $token;
        } catch (\Exception $e) {
            Log::error('Failed to create session token', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Display user library
     */
    public function index()
    {
        try {
            $token = $this->getAuthToken();
            if (!$token) {
                return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
            }

            $response = Http::withToken($token)
                ->get(config('app.url') . '/api/v1/library');

            $libraryItems = [];
            if ($response->successful()) {
                $libraryItems = $response->json('data', []);
            }

            return view('frontend.library.index', [
                'libraryItems' => $libraryItems,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch library', [
                'error' => $e->getMessage(),
            ]);

            return view('frontend.library.index', [
                'libraryItems' => [],
            ]);
        }
    }

    /**
     * Open book reader
     */
    public function read(string $bookId)
    {
        try {
            $token = $this->getAuthToken();
            if (!$token) {
                return redirect()->route('login');
            }

            // Check if user has access by fetching library
            $response = Http::withToken($token)
                ->get(config('app.url') . '/api/v1/library');

            if ($response->successful()) {
                $libraryItems = $response->json('data', []);
                $hasAccess = collect($libraryItems)->contains('book_id', $bookId);

                if (!$hasAccess) {
                    return redirect()->route('library.index')->with('error', 'Anda tidak memiliki akses ke buku ini');
                }
            } else {
                return redirect()->route('library.index')->with('error', 'Gagal memverifikasi akses');
            }

            // Get book details from Catalog
            $catalogService = app(\App\Modules\Catalog\Services\CatalogService::class);
            $book = $catalogService->getBookDetail($bookId);

            if (!$book) {
                return redirect()->route('library.index')->with('error', 'Buku tidak ditemukan');
            }

            // Create viewer session for streaming
            $viewerService = app(\App\Modules\Library\Services\ViewerService::class);
            $viewerSession = $viewerService->createSession(
                auth()->id(),
                $bookId,
                'pdf', // default format
                request()->userAgent() ?? 'web',
                request()->ip()
            );

            return view('frontend.library.read', [
                'book' => $book,
                'bookId' => $bookId,
                'viewerToken' => $viewerSession->token,
                'streamUrl' => route('library.stream', $viewerSession->token),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to open book reader', [
                'book_id' => $bookId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('library.index')->with('error', 'Terjadi kesalahan');
        }
    }
}
