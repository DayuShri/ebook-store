<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Catalog\Models\BookCategory;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * List kategori
     */
    public function index()
    {
        $categories = BookCategory::orderBy('name')->paginate(10);

        return view('frontend.catalog.categories.index', compact('categories'));
    }

    /**
     * Form create
     */
    public function create()
    {
        $parents = BookCategory::orderBy('name')->get();

        return view(
            'frontend.catalog.categories.create',
            compact('parents')
        );
    }

    /**
     * Simpan kategori baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'slug'      => ['nullable', 'string', 'max:120', 'unique:book_categories,slug'],
            'parent_id' => ['nullable', 'exists:book_categories,id'],
        ]);

        BookCategory::create([
            'id'        => (string) Str::uuid(),
            'name'      => $validated['name'],
            'slug'      => $validated['slug'] ?? Str::slug($validated['name']),
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return redirect()
            ->route('catalog.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Form edit
     */
    public function edit(BookCategory $category)
    {
        $parents = BookCategory::where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view(
            'frontend.catalog.categories.edit',
            compact('category', 'parents')
        );
    }

    /**
     * Update kategori
     */
    public function update(Request $request, BookCategory $category)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'slug'      => [
                'nullable',
                'string',
                'max:120',
                'unique:book_categories,slug,' . $category->id . ',id'
            ],
            'parent_id' => ['nullable', 'exists:book_categories,id'],
        ]);

        $category->update([
            'name'      => $validated['name'],
            'slug'      => $validated['slug'] ?? Str::slug($validated['name']),
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return redirect()
            ->route('catalog.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori
     */
    public function destroy(BookCategory $category)
    {
        $category->delete();

        return redirect()
            ->route('catalog.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    public function show(BookCategory $category)
    {
        // ambil parent (jika ada)
        $category->load('parent', 'children');

        return view(
            'frontend.catalog.categories.show',
            compact('category')
        );
    }

}
