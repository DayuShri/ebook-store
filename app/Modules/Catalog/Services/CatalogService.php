<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Book;
use Illuminate\Support\Str;

use App\Modules\Catalog\Services\LibraryClient;


class CatalogService
{
    /**
     * Create new book with categories
     */
    public function createBook(array $data): Book
    {
        $data['id'] = $data['id'] ?? (string) Str::uuid();

        $book = Book::create($data);

        if (!empty($data['category_ids'])) {
            $book->categories()->sync($data['category_ids']);
        }

        return $book->load('categories');
    }

    /**
     * Update book & sync categories
     */
    public function updateBook(Book $book, array $data): Book
    {
        $book->update($data);

        if (array_key_exists('category_ids', $data)) {
            $book->categories()->sync($data['category_ids'] ?? []);
        }

        return $book->load('categories');
    }

    /**
     * Delete book
     */
    public function deleteBook(Book $book): void
    {
        $book->categories()->detach();
        $book->delete();
    }

    /**
     * Public list books
     */
    public function listBooks()
    {
        return Book::with('categories')
            ->where('is_active', true)
            ->paginate(10);
    }

    /**
     * Public book detail
     */
    public function getBookDetail(string $id): Book
    {
        return Book::with('categories')->findOrFail($id);
    }

    public function getPublicBooks(array $filters = [])
    {
        $query = Book::query()
            ->where('is_active', true)
            ->with('categories');

        // optional filter
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate(10);
    }



    
    protected LibraryClient $library;

    public function __construct(LibraryClient $library)
    {
        $this->library = $library;
    }

    public function detail(string $id, ?string $token = null)
    {
        $book = Book::with('categories')->find($id);
        if (! $book) {
            return null;
        }

        $files = $this->library->getBookFiles($id, $token);

        return [
            'id' => $book->id,
            'title' => $book->title,
            'price' => $book->price,
            'categories' => $book->categories,
            'files' => $files
        ];
    }

}
