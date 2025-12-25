<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCategory;

class HomeController extends Controller
{
    public function index()
    {
        // Tentukan relasi yang benar-benar ada di model
        $withBook = [];
        if (method_exists(Book::class, 'categories')) $withBook[] = 'categories';
        if (method_exists(Book::class, 'authors'))    $withBook[] = 'authors';

        // Tentukan kolom rating yang benar-benar ada
        $bookTable = (new Book)->getTable();
        $ratingColumn = Schema::hasColumn($bookTable, 'avg_rating') ? 'avg_rating' : null;

        // 1) Featured / Popular
        $featuredQuery = Book::query();
        if (!empty($withBook)) $featuredQuery->with($withBook);

        if ($ratingColumn) {
            $featuredQuery->orderByDesc($ratingColumn);
        } else {
            $featuredQuery->latest(); // fallback aman kalau avg_rating tidak ada
        }

        $featuredBooks = $featuredQuery
            ->take(8)
            ->get()
            ->map(fn($b) => $this->toBookArray($b));

        // 2) Newest
        $newestQuery = Book::query();
        if (!empty($withBook)) $newestQuery->with($withBook);

        $newestBooks = $newestQuery
            ->latest()
            ->take(8)
            ->get()
            ->map(fn($b) => $this->toBookArray($b));

        // 3) Categories + count buku (fallback kalau relasi books belum ada)
        if (method_exists(BookCategory::class, 'books')) {
            $categories = BookCategory::withCount('books')
                ->orderBy('name')
                ->get()
                ->map(fn($c) => [
                    'id'    => $c->id,
                    'name'  => $c->name,
                    'slug'  => $c->slug,
                    'count' => (int) $c->books_count,
                ]);
        } else {
            // fallback: tampilkan kategori tanpa count
            $categories = BookCategory::orderBy('name')
                ->get()
                ->map(fn($c) => [
                    'id'    => $c->id,
                    'name'  => $c->name,
                    'slug'  => $c->slug,
                    'count' => null,
                ]);
        }

        return view('frontend.home.index', compact('featuredBooks', 'newestBooks', 'categories'));
    }

    private function toBookArray($b): array
    {
        // cover_image_url mungkin namanya beda di DB kamu
        $cover = $b->cover_image_url ?? $b->cover ?? $b->cover_image ?? null;

        $categories = [];
        if (isset($b->categories)) {
            $categories = $b->categories->map(fn($c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ])->values()->all();
        }

        $authors = [];
        if (isset($b->authors)) {
            $authors = $b->authors->map(fn($a) => [
                'id'   => $a->id,
                'name' => $a->name,
            ])->values()->all();
        }

        return [
            'id' => $b->id,
            'title' => $b->title,
            'cover_image_url' => $cover,
            'price' => (float) ($b->price ?? 0),
            'discount_percentage' => (float) ($b->discount_percentage ?? 0),
            'avg_rating' => isset($b->avg_rating) ? (float) $b->avg_rating : null,
            'categories' => $categories,
            'authors' => $authors,
        ];
    }
}
