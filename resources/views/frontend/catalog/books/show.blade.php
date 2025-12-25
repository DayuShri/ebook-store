@extends('frontend.layouts.app')

@section('title', 'Detail Buku')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-10">

    {{-- ================= HEADER ================= --}}
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
                Detail Buku
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Informasi lengkap buku di katalog.
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('catalog.books.edit', $book) }}"
               class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                Edit
            </a>

            <a href="{{ route('catalog.books.index') }}"
               class="rounded-xl border px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-6 md:p-8 space-y-12">

        {{-- ================= INFORMASI UTAMA ================= --}}
        <section>
            <h2 class="text-lg font-semibold text-slate-900 mb-4">
                Informasi Utama
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div>
                    <div class="text-slate-500 mb-1">Judul</div>
                    <div class="font-semibold text-slate-900">
                        {{ $book->title }}
                    </div>
                </div>

                <div>
                    <div class="text-slate-500 mb-1">ISBN</div>
                    <div class="text-slate-900">
                        {{ $book->isbn ?? '—' }}
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <div class="text-slate-500 mb-1">Subtitle</div>
                <div class="text-slate-900">
                    {{ $book->subtitle ?? '—' }}
                </div>
            </div>

            <div class="mt-6">
                <div class="text-slate-500 mb-1">Sinopsis</div>
                <div class="text-slate-900 whitespace-pre-line">
                    {{ $book->synopsis ?? '—' }}
                </div>
            </div>
        </section>

        {{-- ================= DETAIL PUBLIKASI ================= --}}
        <section>
            <h2 class="text-lg font-semibold text-slate-900 mb-4">
                Detail Publikasi
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <div class="text-slate-500 mb-1">Penulis</div>
                    <div>{{ $book->author ?? '—' }}</div>
                </div>

                <div>
                    <div class="text-slate-500 mb-1">Penerbit</div>
                    <div>{{ $book->publisher ?? '—' }}</div>
                </div>

                <div>
                    <div class="text-slate-500 mb-1">Tanggal Terbit</div>
                    <div>
                        {{ $book->publication_date
                            ? \Carbon\Carbon::parse($book->publication_date)->format('d M Y')
                            : '—' }}
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= FILE & TEKNIS ================= --}}
        <section>
            <h2 class="text-lg font-semibold text-slate-900 mb-4">
                File & Teknis
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <div class="text-slate-500 mb-1">Format File</div>
                    <div>{{ $book->file_format ?? '—' }}</div>
                </div>

                <div>
                    <div class="text-slate-500 mb-1">Ukuran File</div>
                    <div>
                        {{ $book->file_size_mb ? $book->file_size_mb . ' MB' : '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-slate-500 mb-1">Jumlah Halaman</div>
                    <div>{{ $book->page_count ?? '—' }}</div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div>
                    <div class="text-slate-500 mb-1">Bahasa</div>
                    <div>{{ $book->language ?? '—' }}</div>
                </div>

                <div>
                    <div class="text-slate-500 mb-1">Cover Image</div>
                    @if($book->cover_image_url)
                        <a href="{{ $book->cover_image_url }}" target="_blank"
                           class="text-indigo-600 hover:underline">
                            Lihat Cover
                        </a>
                    @else
                        —
                    @endif
                </div>
            </div>
        </section>

        {{-- ================= HARGA, STATUS & KATEGORI ================= --}}
        <section>
            <h2 class="text-lg font-semibold text-slate-900 mb-4">
                Harga, Status & Kategori
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <div class="text-slate-500 mb-1">Harga</div>
                    <div class="font-semibold">
                        Rp {{ number_format($book->price, 0, ',', '.') }}
                    </div>
                </div>

                <div>
                    <div class="text-slate-500 mb-1">Diskon</div>
                    <div>
                        {{ $book->discount_percentage
                            ? $book->discount_percentage . '%'
                            : '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-slate-500 mb-1">Status</div>
                    @if($book->is_active)
                        <span class="inline-flex items-center rounded-full
                                     bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full
                                     bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                            Nonaktif
                        </span>
                    @endif
                </div>
            </div>

            <div class="mt-6">
                <div class="text-slate-500 mb-2">Kategori</div>

                <div class="flex flex-wrap gap-2">
                    @forelse($book->categories as $category)
                        <span class="inline-flex items-center rounded-full
                                     bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                            {{ $category->name }}
                        </span>
                    @empty
                        <span class="text-sm text-slate-400">Tidak ada kategori</span>
                    @endforelse
                </div>
            </div>
        </section>

    </div>
</div>
@endsection
