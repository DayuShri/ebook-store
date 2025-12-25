<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCategory;
use App\Services\LibraryFileUploader;

class BookController extends Controller
{
    /* ===============================
     * LIST BUKU
     * =============================== */
    public function index(Request $request)
    {
        $books = Book::with('categories')
            ->when($request->search, fn ($q) =>
                $q->where('title', 'like', "%{$request->search}%")
            )
            ->latest()
            ->paginate(10);

        return view('frontend.catalog.books.index', compact('books'));
    }

    /* ===============================
     * FORM CREATE
     * =============================== */
    public function create()
    {
        $categories = BookCategory::orderBy('name')->get();
        return view('frontend.catalog.books.create', compact('categories'));
    }

    /* ===============================
     * STORE (CREATE + UPLOAD FILE) - OPSI A (INTERNAL, TANPA HTTP)
     * =============================== */
    public function store(Request $request, LibraryFileUploader $uploader)
    {
        $data = $request->validate([
            'isbn' => 'nullable|string|max:20|unique:books,isbn',
            'title' => 'required|string|max:500',
            'subtitle' => 'nullable|string|max:500',
            'synopsis' => 'nullable|string',
            'author' => 'nullable|string|max:500',
            'publisher' => 'nullable|string|max:255',
            'cover_image_url' => 'nullable|url',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'publication_date' => 'nullable|date',
            'page_count' => 'nullable|integer|min:1',
            'language' => 'nullable|string|max:10',
            'file_format' => 'nullable|string|max:20',
            'file_size_mb' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:book_categories,id',
            'book_file' => 'required|file|mimes:pdf|max:102400',
        ]);

        try {
            $book = null;

            DB::transaction(function () use (&$book, $data, $request, $uploader) {
                // 1) default values
                $data['is_active'] = $request->boolean('is_active');
                $data['discount_percentage'] = $data['discount_percentage'] ?? 0;

                // 2) create book (Eloquent akan ignore field yang bukan fillable)
                $book = Book::create($data);

                // 3) sync category
                $book->categories()->sync($data['category_ids']);

                // 4) upload file secara INTERNAL (tanpa HTTP)
                $bookFile = $uploader->upload($book->id, $request->file('book_file'));

                // 5) (opsional tapi bagus) sinkron metadata di tabel books biar konsisten
                $book->update([
                    'file_format'  => $bookFile->file_format,   // PDF
                    'file_size_mb' => $bookFile->file_size_mb,
                ]);
            });

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['upload' => $e->getMessage()]);
        }

        return redirect()
            ->route('catalog.books.index')
            ->with('success', 'Buku dan file berhasil ditambahkan');
    }

    /* ===============================
     * DETAIL
     * =============================== */
    public function show(Book $book)
    {
        $book->load('categories');

        return view('frontend.catalog.books.show', compact('book'));
    }

    /* ===============================
     * FORM EDIT
     * =============================== */
    public function edit(Book $book)
    {
        $categories = BookCategory::orderBy('name')->get();
        $book->load('categories');

        return view('frontend.catalog.books.edit', compact('book', 'categories'));
    }

    /* ===============================
     * UPDATE + OPTIONAL REPLACE FILE - OPSI A (INTERNAL, TANPA HTTP)
     * =============================== */
    public function update(Request $request, Book $book, LibraryFileUploader $uploader)
    {
        $data = $request->validate([
            'isbn' => 'nullable|string|max:20|unique:books,isbn,' . $book->id,
            'title' => 'required|string|max:500',
            'subtitle' => 'nullable|string|max:500',
            'synopsis' => 'nullable|string',
            'author' => 'nullable|string|max:500',
            'publisher' => 'nullable|string|max:255',
            'cover_image_url' => 'nullable|url',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'publication_date' => 'nullable|date',
            'page_count' => 'nullable|integer|min:1',
            'language' => 'nullable|string|max:10',
            'file_format' => 'nullable|string|max:20',
            'file_size_mb' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:book_categories,id',
            'book_file' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        try {
            DB::transaction(function () use ($data, $request, $book, $uploader) {
                $data['is_active'] = $request->boolean('is_active');
                $data['discount_percentage'] = $data['discount_percentage'] ?? 0;

                // 1) update book
                $book->update($data);

                // 2) sync category
                $book->categories()->sync($data['category_ids']);

                // 3) replace file (kalau ada)
                if ($request->hasFile('book_file')) {
                    $bookFile = $uploader->upload($book->id, $request->file('book_file'));

                    $book->update([
                        'file_format'  => $bookFile->file_format,   // PDF
                        'file_size_mb' => $bookFile->file_size_mb,
                    ]);
                }
            });

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['update' => $e->getMessage()]);
        }

        return redirect()
            ->route('catalog.books.index')
            ->with('success', 'Buku berhasil diperbarui');
    }

    /* ===============================
     * DELETE
     * =============================== */
    public function destroy(Book $book)
    {
        DB::transaction(function () use ($book) {
            $book->categories()->detach();
            $book->delete();
        });

        return redirect()
            ->route('catalog.books.index')
            ->with('success', 'Buku berhasil dihapus');
    }

    /* ============================================================
     * ROUTE COMPATIBILITY (kalau route upload step2 masih ada)
     * OPSI A tidak pakai ini, tapi biar route kamu tidak “nganggur”
     * ============================================================ */
    public function uploadForm(Book $book)
    {
        return redirect()->route('catalog.books.edit', $book->id);
    }

    public function uploadStore(Request $request, Book $book)
    {
        return redirect()->route('catalog.books.edit', $book->id);
    }
}
