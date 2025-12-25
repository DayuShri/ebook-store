<?php

namespace App\Modules\Catalog\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\BookCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Public / user - list categories
     */
    public function index(): JsonResponse
    {
        $categories = BookCategory::with('children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Public / user - books by category
     */
    public function books(string $id): JsonResponse
    {
        $category = BookCategory::with([
            'books' => function ($query) {
                $query->where('is_active', true);
            }
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Admin - create category
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:book_categories,slug',
            'parent_id' => 'nullable|exists:book_categories,id',
        ]);

        $data['id'] = (string) Str::uuid();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $category = BookCategory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Admin - update category
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $category = BookCategory::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:book_categories,slug,' . $category->id . ',id',
            'parent_id' => 'nullable|exists:book_categories,id',
        ]);

        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category->fresh(),
        ]);
    }

    /**
     * Admin - delete category
     */
    public function destroy(string $id): JsonResponse
    {
        $category = BookCategory::findOrFail($id);

        // optional safety check
        if ($category->books()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Category cannot be deleted because it has books',
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
