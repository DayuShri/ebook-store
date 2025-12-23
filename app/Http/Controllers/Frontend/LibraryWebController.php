<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Modules\Library\Services\LibraryService;
use App\Modules\Library\Services\ViewerService;
use App\Modules\Review_Reading\Services\ReadingService;
use App\Modules\Review_Reading\Services\ReviewService;
use App\Modules\Review_Reading\Models\ReadingProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LibraryWebController extends Controller
{
    protected LibraryService $libraryService;
    protected ViewerService $viewerService;
    protected ReadingService $readingService;
    protected ReviewService $reviewService;

    public function __construct(
        LibraryService $libraryService,
        ViewerService $viewerService,
        ReadingService $readingService,
        ReviewService $reviewService
    ) {
        $this->libraryService = $libraryService;
        $this->viewerService = $viewerService;
        $this->readingService = $readingService;
        $this->reviewService = $reviewService;
    }

    /**
     * Display user's library with their books
     */
    public function index()
    {
        try {
            $userId = auth()->id();
            
            // Get library items from database
            $libraryItems = $this->libraryService->listUserLibrary($userId);
            
            // Enrich with book details from catalog service
            $enrichedItems = [];
            foreach ($libraryItems as $item) {
                $bookDetails = $this->getBookDetails($item->book_id);
                
                // Get reading progress
                $progress = ReadingProgress::where('user_id', $userId)
                    ->where('book_id', $item->book_id)
                    ->first();
                
                $enrichedItems[] = [
                    'library_item' => $item,
                    'book' => $bookDetails,
                    'progress' => $progress ? [
                        'last_page_read' => $progress->last_page_read,
                        'total_pages' => $progress->total_pages,
                        'progress_percentage' => $progress->progress_percentage,
                        'last_read_at' => $progress->last_read_at,
                    ] : null,
                ];
            }

            return view('frontend.library.index', [
                'libraryItems' => $enrichedItems,
            ]);
        } catch (\Exception $e) {
            Log::error('Library index error: ' . $e->getMessage());
            
            return view('frontend.library.index', [
                'libraryItems' => [],
                'error' => 'Terjadi kesalahan saat memuat perpustakaan',
            ]);
        }
    }

    /**
     * Show book reader
     */
    public function read(Request $request, string $bookId)
    {
        try {
            $userId = auth()->id();
            
            // Check if user has access
            $libraryItem = \App\Modules\Library\Models\LibraryItem::where('user_id', $userId)
                ->where('book_id', $bookId)
                ->where('status', 'ACTIVE')
                ->first();
            
            if (!$libraryItem) {
                return redirect()->route('library.index')
                    ->with('error', 'Anda tidak memiliki akses ke buku ini');
            }

            // Get book details
            $bookDetails = $this->getBookDetails($bookId);
            
            // Create viewer session for streaming from Supabase
            $viewerSession = $this->viewerService->createSession(
                $userId,
                $bookId,
                'pdf', // default format
                $request->userAgent() ?? 'web',
                $request->ip()
            );
            
            // Start reading session
            try {
                $this->readingService->start($userId, $bookId, $request->userAgent());
            } catch (\Exception $e) {
                Log::warning('Failed to start reading session: ' . $e->getMessage());
            }
            
            // Get or create reading progress
            $progress = ReadingProgress::firstOrCreate(
                ['user_id' => $userId, 'book_id' => $bookId],
                [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'total_pages' => $bookDetails['page_count'] ?? 200,
                    'last_page_read' => 0,
                    'progress_percentage' => 0,
                    'last_read_at' => now(),
                ]
            );

            return view('frontend.library.reader', [
                'book' => $bookDetails,
                'progress' => $progress,
                'bookId' => $bookId,
                'viewerToken' => $viewerSession->token,
                'streamUrl' => route('library.stream', $viewerSession->token),
            ]);
        } catch (\Exception $e) {
            Log::error('Reader error: ' . $e->getMessage());
            
            return redirect()->route('library.index')
                ->with('error', 'Terjadi kesalahan saat membuka buku: ' . $e->getMessage());
        }
    }

    /**
     * Update reading progress (AJAX)
     */
    public function updateProgress(Request $request, string $bookId)
    {
        try {
            $validated = $request->validate([
                'last_page_read' => 'required|integer|min:0',
                'total_pages' => 'required|integer|min:1',
            ]);

            $userId = auth()->id();
            
            $progress = ReadingProgress::where('user_id', $userId)
                ->where('book_id', $bookId)
                ->first();

            if (!$progress) {
                return response()->json(['error' => 'Progress not found'], 404);
            }

            $lastPage = min($validated['last_page_read'], $validated['total_pages']);
            $percentage = round(($lastPage / $validated['total_pages']) * 100, 2);
            
            $progress->update([
                'last_page_read' => $lastPage,
                'total_pages' => $validated['total_pages'],
                'progress_percentage' => $percentage,
                'last_read_at' => now(),
            ]);

            // Update reading session via ReadingService
            try {
                $this->readingService->finish($userId, $bookId, $lastPage);
            } catch (\Exception $e) {
                Log::warning('Failed to finish reading session: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Progress berhasil disimpan',
                'progress' => [
                    'last_page_read' => $progress->last_page_read,
                    'total_pages' => $progress->total_pages,
                    'progress_percentage' => $progress->progress_percentage,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Update progress error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Gagal menyimpan progress',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get book reviews
     */
    public function reviews(string $bookId)
    {
        try {
            $userId = auth()->id();
            
            // Get book details
            $bookDetails = $this->getBookDetails($bookId);
            
            // Get reviews
            $reviews = $this->reviewService->getByBook($bookId);
            
            // Check if user has this book
            $hasAccess = \App\Modules\Library\Models\LibraryItem::where('user_id', $userId)
                ->where('book_id', $bookId)
                ->where('status', 'ACTIVE')
                ->exists();
            
            // Get user's review
            $userReview = $reviews->where('user_id', $userId)->first();

            return view('frontend.library.book-reviews', [
                'book' => $bookDetails,
                'reviews' => $reviews,
                'hasAccess' => $hasAccess,
                'userReview' => $userReview,
                'bookId' => $bookId,
            ]);
        } catch (\Exception $e) {
            Log::error('Reviews error: ' . $e->getMessage());
            
            return redirect()->route('library.index')
                ->with('error', 'Terjadi kesalahan saat memuat review');
        }
    }

    /**
     * Store review
     */
    public function storeReview(Request $request, string $bookId)
    {
        try {
            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'review_text' => 'nullable|string|max:1000',
            ]);

            $userId = auth()->id();
            
            $this->reviewService->store($userId, [
                'book_id' => $bookId,
                'rating' => $validated['rating'],
                'review_text' => $validated['review_text'],
            ]);

            return redirect()->route('library.reviews', $bookId)
                ->with('success', 'Review berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Store review error: ' . $e->getMessage());
            
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get book details from Catalog module (internal) or database
     */
    private function getBookDetails(string $bookId): array
    {
        // Use internal Catalog BookService directly (same application)
        try {
            $bookService = app(\App\Modules\Catalog\Services\BookService::class);
            $book = $bookService->detail($bookId);
            
            if ($book) {
                // Handle author - single string field in Catalog model
                $authors = [];
                if (!empty($book->author)) {
                    $authors = [['name' => $book->author]];
                }
                
                return [
                    'id' => $book->id,
                    'title' => $book->title ?? 'Untitled',
                    'subtitle' => $book->subtitle,
                    'synopsis' => $book->synopsis,
                    'authors' => $authors ?: [['name' => 'Unknown Author']],
                    'cover_image_url' => $book->cover_image_url,
                    'description' => $book->synopsis,
                    'page_count' => $book->page_count ?? 200,
                    'price' => $book->price,
                    'publication_date' => $book->publication_date,
                ];
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning("Book not found in Catalog module: {$bookId}");
        } catch (\Exception $e) {
            Log::warning('Failed to get book from Catalog module: ' . $e->getMessage());
        }

        // Fallback: Try direct database query
        try {
            $book = \Illuminate\Support\Facades\DB::table('books')
                ->where('id', $bookId)
                ->first();
            
            if ($book) {
                $authors = [];
                if (!empty($book->author)) {
                    $authors = [['name' => $book->author]];
                }
                
                return [
                    'id' => $book->id,
                    'title' => $book->title ?? 'Untitled',
                    'subtitle' => $book->subtitle ?? null,
                    'synopsis' => $book->synopsis ?? null,
                    'authors' => $authors ?: [['name' => 'Unknown Author']],
                    'cover_image_url' => $book->cover_image_url ?? null,
                    'description' => $book->synopsis ?? null,
                    'page_count' => $book->page_count ?? 200,
                    'price' => $book->price ?? null,
                    'publication_date' => $book->publication_date ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get book from database: ' . $e->getMessage());
        }

        // Return fallback data if book not found anywhere
        Log::error("Book not found: {$bookId}");
        return [
            'id' => $bookId,
            'title' => 'Book Not Found',
            'authors' => [['name' => 'Unknown']],
            'cover_image_url' => null,
            'description' => 'Book details not available',
            'page_count' => 200,
        ];
    }
}
