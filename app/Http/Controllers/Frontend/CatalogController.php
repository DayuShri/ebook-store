<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\CatalogService;
use App\Services\Frontend\CartService;
use App\Services\Frontend\ReviewService;
use App\Services\Frontend\UserFrontendService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    protected CatalogService $catalogService;
    protected CartService $cartService;
    protected ReviewService $reviewService;
    protected UserFrontendService $userService;

    public function __construct(
        CatalogService $catalogService,
        CartService $cartService,
        ReviewService $reviewService,
        UserFrontendService $userService
    ) {
        $this->catalogService = $catalogService;
        $this->cartService = $cartService;
        $this->reviewService = $reviewService;
        $this->userService = $userService;
    }

    /**
     * Display book catalog with filters
     */
    public function index(Request $request)
    {
        $filters = [
            'category' => $request->get('category'),
            'categories' => $request->get('categories', []),
            'price_min' => $request->get('price_min'),
            'price_max' => $request->get('price_max'),
            'min_rating' => $request->get('min_rating'),
            'sort' => $request->get('sort', 'newest'),
            'q' => $request->get('q'),
        ];

        $books = $this->catalogService->getBooks($filters);
        $categories = $this->catalogService->getCategories();

        return view('frontend.catalog.index', [
            'books' => $books,
            'categories' => $categories,
            'filters' => $filters,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Display book detail page
     */
    public function show(string $id)
    {
        $book = $this->catalogService->getBook($id);

        if (!$book) {
            abort(404, 'Buku tidak ditemukan');
        }

        $reviewsData = $this->reviewService->getBookReviews($id);
        $isInWishlist = auth()->check() ? $this->userService->isInWishlist($id) : false;

        // Get related books from same category
        $relatedBooks = [];
        if (!empty($book['categories'])) {
            $relatedBooks = $this->catalogService->getBooks([
                'category' => $book['categories'][0]['slug'],
            ]);
            // Remove current book from related
            $relatedBooks = array_filter($relatedBooks, fn($b) => $b['id'] !== $id);
            $relatedBooks = array_slice($relatedBooks, 0, 4);
        }

        return view('frontend.catalog.show', [
            'book' => $book,
            'reviews' => $reviewsData['reviews'],
            'reviewStats' => [
                'avg_rating' => $reviewsData['avg_rating'],
                'total' => $reviewsData['total'],
                'distribution' => $reviewsData['rating_distribution'],
            ],
            'isInWishlist' => $isInWishlist,
            'relatedBooks' => $relatedBooks,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Search books
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $books = $this->catalogService->searchBooks($query);
        $categories = $this->catalogService->getCategories();

        return view('frontend.catalog.index', [
            'books' => $books,
            'categories' => $categories,
            'filters' => ['q' => $query],
            'searchQuery' => $query,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Browse by category
     */
    public function category(string $slug)
    {
        $books = $this->catalogService->getBooks(['category' => $slug]);
        $categories = $this->catalogService->getCategories();

        // Find category name
        $categoryName = $slug;
        foreach ($categories as $cat) {
            if ($cat['slug'] === $slug) {
                $categoryName = $cat['name'];
                break;
            }
            foreach ($cat['children'] ?? [] as $child) {
                if ($child['slug'] === $slug) {
                    $categoryName = $child['name'];
                    break 2;
                }
            }
        }

        return view('frontend.catalog.index', [
            'books' => $books,
            'categories' => $categories,
            'filters' => ['category' => $slug],
            'pageTitle' => 'Kategori: ' . $categoryName,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }
}
