<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Services\CatalogService;
use App\Modules\Catalog\Models\BookCategory;
use App\Services\Frontend\UserFrontendService;

class HomeController extends Controller
{
    protected $catalogService;
    protected $userService;

    public function __construct(
        CatalogService $catalogService,
        UserFrontendService $userService
    ) {
        $this->catalogService = $catalogService;
        $this->userService = $userService;
    }

    public function index()
    {
        // 1) Featured / Popular books with ratings
        $featuredBooks = $this->catalogService->getPublicBooks([])
            ->take(8)
            ->map(fn($b) => $this->toBookArray($b));

        // 2) Newest books with ratings
        $newestBooks = $this->catalogService->getPublicBooks([])
            ->sortByDesc('created_at')
            ->take(8)
            ->map(fn($b) => $this->toBookArray($b));

        // 3) Categories + count
        $categories = BookCategory::withCount('books')
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id'    => $c->id,
                'name'  => $c->name,
                'slug'  => $c->slug,
                'count' => (int) $c->books_count,
            ]);

        return view('frontend.home.index', compact('featuredBooks', 'newestBooks', 'categories'));
    }

    private function toBookArray($b): array
    {
        $cover = $b->cover_image_url ?? $b->cover ?? $b->cover_image ?? null;

        $categories = [];
        if (isset($b->categories)) {
            $categories = $b->categories->map(fn($c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ])->values()->all();
        }

        // Check wishlist status for authenticated users
        $isInWishlist = false;
        if (auth()->check()) {
            $isInWishlist = $this->userService->isInWishlist($b->id);
        }

        return [
            'id' => $b->id,
            'title' => $b->title,
            'author' => $b->author,
            'cover_image_url' => $cover,
            'price' => (float) ($b->price ?? 0),
            'discount_percentage' => (float) ($b->discount_percentage ?? 0),
            'avg_rating' => $b->avg_rating, // Now comes from CatalogService
            'is_in_wishlist' => $isInWishlist,
            'categories' => $categories,
        ];
    }
}
