<?php

namespace App\Modules\Catalog\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $service
    ) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->list()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:book_categories,slug',
            'parent_id' => 'nullable|exists:book_categories,id',
        ]);

        // id uuid auto by HasUuids, jadi gak perlu Str::uuid() kalau model pakai HasUuids
        $cat = $this->service->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category created',
            'data' => $cat
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'slug' => 'sometimes|string|max:100|unique:book_categories,slug,' . $id,
            'parent_id' => 'nullable|exists:book_categories,id',
        ]);

        $cat = $this->service->update($id, $data);

        return response()->json([
            'success' => true,
            'message' => 'Category updated',
            'data' => $cat
        ]);
    }

    public function show($id)
{
    return response()->json([
        'success' => true,
        'data' => $this->service->detail($id)
    ]);
}


    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted'
        ]);
    }
}
