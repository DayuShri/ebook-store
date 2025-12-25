@extends('frontend.layouts.app')

@section('title', 'Edit Buku')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-10">

    {{-- ================= HEADER ================= --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
            Edit Buku
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Perbarui data buku di katalog.
        </p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200">
        <form method="POST"
              action="{{ route('catalog.books.update', $book) }}"
              enctype="multipart/form-data"
              class="p-6 md:p-8 space-y-12">
            @csrf
            @method('PUT')

            {{-- ================= ERROR ================= --}}
            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <div class="font-semibold mb-1">Ada yang perlu diperbaiki:</div>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ================= INFORMASI UTAMA ================= --}}
            <section>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    Informasi Utama
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Judul Buku
                        </label>
                        <input name="title" required
                               value="{{ old('title', $book->title) }}"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            ISBN
                        </label>
                        <input name="isbn"
                               value="{{ old('isbn', $book->isbn) }}"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Subtitle
                    </label>
                    <input name="subtitle"
                           value="{{ old('subtitle', $book->subtitle) }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                  focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Sinopsis
                    </label>
                    <textarea name="synopsis" rows="4"
                              class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                     focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('synopsis', $book->synopsis) }}</textarea>
                </div>
            </section>

            {{-- ================= DETAIL PUBLIKASI ================= --}}
            <section>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    Detail Publikasi
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Penulis
                        </label>
                        <input name="author"
                               value="{{ old('author', $book->author) }}"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Penerbit
                        </label>
                        <input name="publisher"
                               value="{{ old('publisher', $book->publisher) }}"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Tanggal Terbit
                        </label>
                        <input type="date" name="publication_date"
                               value="{{ old('publication_date', optional($book->publication_date)->format('Y-m-d')) }}"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </section>

            {{-- ================= FILE & TEKNIS ================= --}}
            <section>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    File & Teknis
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Format File
                        </label>
                        <input name="file_format"
                               value="{{ old('file_format', $book->file_format) }}"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Ukuran File (MB)
                        </label>
                        <input type="number" step="0.01" name="file_size_mb"
                               value="{{ old('file_size_mb', $book->file_size_mb) }}"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Jumlah Halaman
                        </label>
                        <input type="number" name="page_count"
                               value="{{ old('page_count', $book->page_count) }}"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Bahasa
                    </label>
                    <input name="language"
                           value="{{ old('language', $book->language) }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                  focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Cover Image URL
                    </label>
                    <input name="cover_image_url"
                           value="{{ old('cover_image_url', $book->cover_image_url) }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                  focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- OPTIONAL: Upload file buku baru --}}
                <div class="mt-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Upload File Buku Baru (PDF) <span class="text-slate-400 text-xs">(Opsional)</span>
                    </label>
                    <input type="file"
                           name="book_file"
                           accept="application/pdf"
                           class="block w-full text-sm text-slate-600
                                  file:mr-4 file:rounded-lg
                                  file:border-0 file:bg-indigo-50
                                  file:px-4 file:py-2
                                  file:text-sm file:font-semibold
                                  file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-xs text-slate-500 mt-1">
                        Kosongkan jika tidak ingin mengganti file PDF.
                    </p>
                </div>
            </section>

            {{-- ================= HARGA, STATUS & KATEGORI ================= --}}
            <section>
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    Harga, Status & Kategori
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Harga
                        </label>
                        <input type="number" name="price" required
                               value="{{ old('price', $book->price) }}"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Diskon (%)
                        </label>
                        <input type="number" step="0.01" name="discount_percentage"
                               value="{{ old('discount_percentage', $book->discount_percentage) }}"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                @php
                    $selectedCategory = old('category_ids.0', $book->categories->first()?->id);
                @endphp

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Kategori Buku
                    </label>

                    <div class="relative">
                        <select name="category_ids[]" required
                                class="w-full appearance-none rounded-xl
                                       border border-slate-300 bg-white
                                       px-4 py-3 pr-10 text-sm
                                       focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">

                            <option value="" disabled>
                                — Pilih Kategori Buku —
                            </option>

                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected((string)$category->id === (string)$selectedCategory)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 mt-1">
                        Pilih satu kategori utama untuk buku ini.
                    </p>
                </div>

                {{-- ✅ STATUS AKTIF (FIX) --}}
                <div class="mt-6 flex items-center gap-3">
                    {{-- hidden biar kalau checkbox OFF tetap ngirim 0 --}}
                    <input type="hidden" name="is_active" value="0">

                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           @checked(old('is_active', (bool)($book->is_active ?? true)))
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">

                    <span class="text-sm text-slate-700">
                        Aktifkan buku
                    </span>
                </div>
            </section>

            {{-- ================= ACTION ================= --}}
            <div class="flex justify-end gap-3 pt-6">
                <a href="{{ route('catalog.books.index') }}"
                   class="rounded-xl border px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>

                <button type="submit"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
