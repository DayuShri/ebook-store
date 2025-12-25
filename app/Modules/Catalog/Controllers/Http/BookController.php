<?php

namespace App\Modules\Catalog\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Services\CatalogService;
use App\Modules\Catalog\Requests\StoreBookRequest;
use App\Modules\Catalog\Requests\UpdateBookRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    protected CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    /**
     * Public / user - list books
     */
    public function index(Request $request): JsonResponse
    {
        $books = $this->catalogService->getPublicBooks($request->all());

    return response()->json([
        'success' => true,
        'data' => $books->items(), // 👈 hanya data inti
        'meta' => [
            'current_page' => $books->currentPage(),
            'per_page' => $books->perPage(),
            'total' => $books->total(),
            'last_page' => $books->lastPage(),
        ]
    ]);
    }

    /**
     * Public / user - book detail
     */
    public function show(string $id): JsonResponse
    {
        $book = $this->catalogService->getBookDetail($id);

        return response()->json([
            'success' => true,
            'data' => $book,
        ]);
    }

    /**
     * Admin - create book
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = $this->catalogService->createBook($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Book created successfully',
            'data' => $book,
        ], 201);
    }

    /**
     * Admin - update book
     */
    public function update(UpdateBookRequest $request, string $id): JsonResponse
    {
        $book = Book::findOrFail($id);

        $book = $this->catalogService->updateBook($book, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Book updated successfully',
            'data' => $book,
        ]);
    }

    /**
     * Admin - delete book
     */
    public function destroy(string $id): JsonResponse
    {
        $book = Book::findOrFail($id);

        $this->catalogService->deleteBook($book);

        return response()->json([
            'success' => true,
            'message' => 'Book deleted successfully',
        ]);
    }
}
