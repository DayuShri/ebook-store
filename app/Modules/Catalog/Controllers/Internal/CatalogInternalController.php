<?php

namespace App\Modules\Catalog\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogInternalController extends Controller
{
    /**
     * Validate book existence & active status
     * Used by: Order, Wishlist, Library, Review
     */
    public function getBookPrices(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_ids' => 'required|array',
            'book_ids.*' => 'string',
        ]);

        $books = Book::whereIn('id', $data['book_ids'])
            ->where('is_active', true)
            ->get(['id', 'price']);

        if ($books->count() !== count($data['book_ids'])) {
            return response()->json([
                'valid' => false,
                'message' => 'One or more books are invalid or inactive',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'books' => $books,
        ]);
    }

    public function getAllBooks()
    {
        $books = Book::where('is_active', true)
            ->with('categories:id,name')
            ->get([
                'id',
                'title',
                'cover_image_url',
                'price'
            ]);

        return response()->json([
            'success' => true,
            'data' => $books
        ]);
    }

    public function getBookDetail(string $id)
    {
        $book = Book::where('id', $id)
            ->where('is_active', true)
            ->with('categories:id,name')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $book
        ]);
    }

    
    public function full($id)
    {
        $book = $this->service->full($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found',
            ], 404);
        }

        return response()->json($book);
    }


}
