@extends('frontend.layouts.app')

@section('title', $pageTitle ?? 'Katalog Buku')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
            {{ $pageTitle ?? ($searchQuery ?? false ? 'Hasil Pencarian: "' . $searchQuery . '"' : 'Katalog Buku') }}
        </h1>
        <p class="text-gray-600 mt-1">
            @if(count($books) > 0)
                Menampilkan {{ count($books) }} buku
            @else
                Tidak ada buku ditemukan
            @endif
        </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        {{-- Sidebar Filters --}}
        @include('frontend.partials.sidebar-filters', ['categories' => $categories])

        {{-- Main Content --}}
        <div class="flex-1">
            {{-- Sort Options --}}
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Urutkan:</span>
                    <select onchange="window.location.href=this.value" 
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option value="{{ route('books.index', array_merge(request()->except('sort'), ['sort' => 'newest'])) }}" 
                                {{ ($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="{{ route('books.index', array_merge(request()->except('sort'), ['sort' => 'rating'])) }}"
                                {{ ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' }}>Rating Tertinggi</option>
                        <option value="{{ route('books.index', array_merge(request()->except('sort'), ['sort' => 'price_low'])) }}"
                                {{ ($filters['sort'] ?? '') === 'price_low' ? 'selected' : '' }}>Harga Terendah</option>
                        <option value="{{ route('books.index', array_merge(request()->except('sort'), ['sort' => 'price_high'])) }}"
                                {{ ($filters['sort'] ?? '') === 'price_high' ? 'selected' : '' }}>Harga Tertinggi</option>
                    </select>
                </div>

                {{-- View Toggle --}}
                <div class="flex items-center space-x-2">
                    <button class="p-2 bg-primary-100 text-primary-700 rounded-lg" title="Grid View">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Books Grid --}}
            @if(count($books) > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($books as $book)
                        @include('frontend.partials.book-card', ['book' => $book])
                    @endforeach
                </div>
            @else
                <div class="text-center py-16">
                    <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Tidak ada buku ditemukan</h3>
                    <p class="mt-2 text-gray-500">Coba ubah filter atau kata kunci pencarian Anda</p>
                    <a href="{{ route('books.index') }}" class="mt-6 inline-block bg-primary-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                        Reset Filter
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
