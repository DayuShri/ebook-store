<?php

namespace App\Modules\Catalog\Services;
use App\Modules\Catalog\Models\BookCategoryMapping;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookAuthor;
use Illuminate\Support\Facades\DB;

class BookService
{
    public function listActive()
    {
        return Book::with('categories')
            ->where('is_active', true)
            ->get();
    }

    public function detail(string $id)
    {
        return Book::with('categories')->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $categoryIds = $data['category_ids'] ?? [];
            $authorIds = $data['author_ids'] ?? [];

            unset($data['category_ids'], $data['author_ids']);

            $book = Book::create($data);

          if (!empty($categoryIds)) {
    foreach ($categoryIds as $categoryId) {
        BookCategoryMapping::create([
            'book_id'     => $book->id,
            'category_id' => $categoryId,
        ]);
    }
}

            // author pivot (tanpa join ke author service)
            foreach ($authorIds as $i => $authorId) {
                BookAuthor::create([
                    'book_id' => $book->id,
                    'author_id' => $authorId,
                    'author_order' => $i + 1,
                ]);
            }

            return $book->load('categories');
        });

        
    }

    public function update(string $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $book = Book::findOrFail($id);

            $categoryIds = $data['category_ids'] ?? null;
            $authorIds = $data['author_ids'] ?? null;

            unset($data['category_ids'], $data['author_ids']);

            $book->update($data);

            if (is_array($categoryIds)) {
                $book->categories()->sync($categoryIds);
            }

            if (is_array($authorIds)) {
                BookAuthor::where('book_id', $book->id)->delete();
                foreach ($authorIds as $i => $authorId) {
                    BookAuthor::create([
                        'book_id' => $book->id,
                        'author_id' => $authorId,
                        'author_order' => $i + 1,
                    ]);
                }
            }

            return $book->load('categories');

            
        });
    }

    public function deactivate(string $id)
    {
        $book = Book::findOrFail($id);
        $book->update(['is_active' => false]);
        return $book;
    }

    // HMVC: Order butuh harga
    public function price(string $id): array
    {
        $book = Book::findOrFail($id);

        return [
            'book_id' => $book->id,
            'price' => (string)$book->price,
            'discount_percentage' => (string)$book->discount_percentage,
            'is_active' => (bool)$book->is_active,
        ];
    }

    // HMVC: cek exists (wishlist/review)
    public function exists(string $id): bool
    {
        return Book::where('id', $id)
            ->where('is_active', true)
            ->exists();
    }

    // HMVC: bulk price (order item banyak)
    public function bulkPrice(array $bookIds)
    {
        return Book::whereIn('id', $bookIds)
            ->get(['id', 'price', 'discount_percentage', 'is_active'])
            ->map(fn($b) => [
                'book_id' => $b->id,
                'price' => (string)$b->price,
                'discount_percentage' => (string)$b->discount_percentage,
                'is_active' => (bool)$b->is_active,
            ]);
    }

    // HMVC: basic info (review/wishlist)
    public function basic(string $id): array
    {
        $book = Book::findOrFail($id);

        return [
            'book_id' => $book->id,
            'title' => $book->title,
            'cover_image_url' => $book->cover_image_url,
            'is_active' => (bool)$book->is_active,
        ];
    }

    // HMVC: full info (wishlist detail)
    public function full(string $id): ?array
    {
        $book = Book::with(['categories', 'authorPivots'])->find($id);

        if (!$book) {
            return null;
        }

        return [
            'book_id' => $book->id,
            'isbn' => $book->isbn,
            'title' => $book->title,
            'subtitle' => $book->subtitle,
            'synopsis' => $book->synopsis,
            'cover_image_url' => $book->cover_image_url,
            'price' => (string)$book->price,
            'discount_percentage' => (string)$book->discount_percentage,
            'publication_date' => $book->publication_date?->toDateString(),
            'page_count' => $book->page_count,
            'language' => $book->language,
            'file_format' => $book->file_format,
            'file_size_mb' => (string)$book->file_size_mb,
            'publisher_id' => $book->publisher_id,
            'is_active' => (bool)$book->is_active,
            'categories' => $book->categories->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ])->toArray(),
            'author_ids' => $book->authorPivots->pluck('author_id')->toArray(),
        ];
    }
}
