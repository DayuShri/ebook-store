<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\LibraryService;
use App\Services\Frontend\CatalogService;
use App\Services\Frontend\CartService;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    protected LibraryService $libraryService;
    protected CatalogService $catalogService;
    protected CartService $cartService;

    public function __construct(
        LibraryService $libraryService,
        CatalogService $catalogService,
        CartService $cartService
    ) {
        $this->libraryService = $libraryService;
        $this->catalogService = $catalogService;
        $this->cartService = $cartService;
    }

    /**
     * Display user library
     */
    public function index()
    {
        $libraryItems = $this->libraryService->getLibraryItems();

        return view('frontend.library.index', [
            'libraryItems' => $libraryItems,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Open book reader
     */
    public function read(string $bookId)
    {
        // Check access
        if (!$this->libraryService->hasAccess($bookId)) {
            return redirect()->route('library.index')->with('error', 'Anda tidak memiliki akses ke buku ini');
        }

        $book = $this->catalogService->getBook($bookId);

        if (!$book) {
            abort(404, 'Buku tidak ditemukan');
        }

        $progress = $this->libraryService->getReadingProgress($bookId);

        return view('frontend.library.reader', [
            'book' => $book,
            'progress' => $progress,
        ]);
    }

    /**
     * Update reading progress (AJAX)
     */
    public function updateProgress(Request $request, string $bookId)
    {
        $request->validate([
            'current_page' => 'required|integer|min:1',
            'total_pages' => 'required|integer|min:1',
        ]);

        $result = $this->libraryService->updateReadingProgress(
            $bookId,
            $request->input('current_page'),
            $request->input('total_pages')
        );

        return response()->json($result);
    }
}
