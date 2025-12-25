<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\CartService;
use App\Services\Frontend\ReviewService;
use App\Services\Frontend\UserFrontendService;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCategory;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected ReviewService $reviewService,
        protected UserFrontendService $userService
    ) {}

    /**
     * Display book catalog with filters (DB langsung)
     */
    public function index(Request $request)
    {
        // 1) Ambil filters dari query string
        $filters = [
            'category'   => $request->get('category'),          // slug (single)
            'categories' => $request->get('categories', []),    // slug[] (checkbox)
            'price_min'  => $request->get('price_min'),
            'price_max'  => $request->get('price_max'),
            'sort'       => $request->get('sort', 'newest'),
            'q'          => $request->get('q'),
        ];

        // 2) Ambil kategori (nested) untuk sidebar
        $categories = BookCategory::with('children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'children' => $c->children->sortBy('name')->map(fn($ch) => [
                    'id' => $ch->id,
                    'name' => $ch->name,
                    'slug' => $ch->slug,
                ])->values()->toArray(),
            ])->values()->toArray();

        // 3) Query buku dari DB
        $query = Book::query()
            ->where('is_active', true)
            ->with(['categories:id,name,slug']);

        // Search
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('author', 'like', "%{$q}%")
                    ->orWhere('isbn', 'like', "%{$q}%");
            });
        }

        // Filter single category (?category=slug)
        if (!empty($filters['category'])) {
            $slug = $filters['category'];
            $query->whereHas('categories', fn($c) => $c->where('slug', $slug));
        }

        // Filter multi categories (?categories[]=slug1&categories[]=slug2)
        if (!empty($filters['categories']) && is_array($filters['categories'])) {
            $slugs = array_values(array_filter($filters['categories']));
            if (count($slugs)) {
                $query->whereHas('categories', fn($c) => $c->whereIn('slug', $slugs));
            }
        }

        // Price range
        if (!empty($filters['price_min'])) {
            $query->where('price', '>=', (float)$filters['price_min']);
        }
        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', (float)$filters['price_max']);
        }

        // Sort
        $sort = $filters['sort'] ?? 'newest';
        if ($sort === 'price_low') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest(); // newest
        }

        // 4) Pagination (penting)
        $books = $query->paginate(12)->withQueryString()

            // 5) Supaya blade kamu yang lama (pakai array) tetap jalan
            ->through(function (Book $b) {
                return [
                    'id' => $b->id,
                    'title' => $b->title,
                    'author' => $b->author,
                    'isbn' => $b->isbn,
                    'price' => (string) $b->price,
                    'cover_image_url' => $b->cover_image_url,
                    'discount_percentage' => $b->discount_percentage,
                    'categories' => $b->categories->map(fn($c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                        'slug' => $c->slug,
                    ])->values()->toArray(),
                ];
            });

        return view('frontend.catalog.index', [
            'books' => $books,
            'categories' => $categories,
            'filters' => $filters,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Book detail page (DB langsung)
     */
    public function show(string $id)
    {
        $bookModel = Book::where('is_active', true)
            ->with(['categories:id,name,slug'])
            ->find($id);

        if (!$bookModel) {
            abort(404, 'Buku tidak ditemukan');
        }

        // Convert ke array biar blade show kamu aman
        $book = [
            'id' => $bookModel->id,
            'title' => $bookModel->title,
            'subtitle' => $bookModel->subtitle,
            'synopsis' => $bookModel->synopsis,
            'author' => $bookModel->author,
            'publisher' => $bookModel->publisher,
            'isbn' => $bookModel->isbn,
            'price' => (string) $bookModel->price,
            'discount_percentage' => $bookModel->discount_percentage,
            'cover_image_url' => $bookModel->cover_image_url,
            'page_count' => $bookModel->page_count,
            'language' => $bookModel->language,
            'file_format' => $bookModel->file_format,
            'file_size_mb' => $bookModel->file_size_mb,
            'categories' => $bookModel->categories->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ])->values()->toArray(),
        ];

        // kalau review/wishlist belum dipakai, boleh kamu comment dulu
        $reviewsData = $this->reviewService->getBookReviews($id);
        $isInWishlist = auth()->check() ? $this->userService->isInWishlist($id) : false;

        // Related books: ambil dari kategori pertama
        $relatedBooks = [];
        if (!empty($book['categories'])) {
            $slug = $book['categories'][0]['slug'];

            $relatedBooks = Book::where('is_active', true)
                ->where('id', '!=', $id)
                ->whereHas('categories', fn($c) => $c->where('slug', $slug))
                ->with('categories:id,name,slug')
                ->limit(4)
                ->get()
                ->map(fn(Book $b) => [
                    'id' => $b->id,
                    'title' => $b->title,
                    'author' => $b->author,
                    'price' => (string) $b->price,
                    'cover_image_url' => $b->cover_image_url,
                    'categories' => $b->categories->map(fn($c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                        'slug' => $c->slug,
                    ])->values()->toArray(),
                ])->values()->toArray();
        }

        return view('frontend.catalog.show', [
            'book' => $book,
            'reviews' => $reviewsData['reviews'] ?? [],
            'reviewStats' => [
                'avg_rating' => $reviewsData['avg_rating'] ?? 0,
                'total' => $reviewsData['total'] ?? 0,
                'distribution' => $reviewsData['rating_distribution'] ?? [],
            ],
            'isInWishlist' => $isInWishlist,
            'relatedBooks' => $relatedBooks,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Search: cukup redirect ke index biar satu sumber data
     */
    public function search(Request $request)
    {
        return redirect()->route('books.index', ['q' => $request->get('q')]);
    }

    /**
     * Category: redirect ke index juga
     */
    public function category(string $slug)
    {
        return redirect()->route('books.index', ['category' => $slug]);
    }
}
