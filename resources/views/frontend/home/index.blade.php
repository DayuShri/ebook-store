@extends('frontend.layouts.app')

@section('title', 'Beranda')

@section('content')
@php
    // aman ambil key dari array/object
    $get = function ($item, string $key, $default = null) {
        if (is_array($item)) return $item[$key] ?? $default;
        if (is_object($item)) return $item->{$key} ?? $default;
        return $default;
    };

    // ambil 3 buku untuk hero
    $featuredForHero = [];
    if (!empty($featuredBooks)) {
        if (is_object($featuredBooks) && method_exists($featuredBooks, 'take')) {
            $featuredForHero = $featuredBooks->take(3);
        } elseif (is_array($featuredBooks)) {
            $featuredForHero = array_slice($featuredBooks, 0, 3);
        }
    }

    $featuredCount = is_object($featuredBooks) && method_exists($featuredBooks, 'count')
        ? $featuredBooks->count()
        : (is_array($featuredBooks) ? count($featuredBooks) : 0);

    $newestCount = is_object($newestBooks) && method_exists($newestBooks, 'count')
        ? $newestBooks->count()
        : (is_array($newestBooks) ? count($newestBooks) : 0);

    $categoryCount = is_object($categories) && method_exists($categories, 'count')
        ? $categories->count()
        : (is_array($categories) ? count($categories) : 0);
@endphp

<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary-600 to-primary-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h1 class="text-4xl md:text-5xl font-bold leading-tight">
                    Baca Buku Digital<br>
                    <span class="text-primary-200">Kapan Saja, Di Mana Saja</span>
                </h1>
                <p class="mt-6 text-lg text-primary-100 max-w-lg">
                    Temukan ribuan buku digital berkualitas dari penulis terbaik Indonesia dan dunia.
                    Baca langsung di browser tanpa perlu download.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('books.index') }}"
                       class="bg-white text-primary-700 px-6 py-3 rounded-lg font-semibold hover:bg-primary-50 transition-colors">
                        Jelajahi Katalog
                    </a>

                    @guest
                        <a href="{{ route('register') }}"
                           class="border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white/10 transition-colors">
                            Daftar Gratis
                        </a>
                    @endguest
                </div>
            </div>

            <div class="hidden md:block relative">
                <div class="grid grid-cols-3 gap-4 transform rotate-3">
                    @forelse($featuredForHero as $book)
                        @php
                            $cover = $get($book, 'cover_image_url');
                            $title = $get($book, 'title', 'Buku');
                        @endphp

                        <div class="rounded-lg overflow-hidden shadow-2xl transform hover:-translate-y-2 transition-transform">
                            @if(!empty($cover))
                                <img src="{{ $cover }}" alt="{{ $title }}" class="w-full aspect-[2/3] object-cover">
                            @else
                                <div class="w-full aspect-[2/3] bg-primary-400"></div>
                            @endif
                        </div>
                    @empty
                        @for($i=0;$i<3;$i++)
                            <div class="rounded-lg overflow-hidden shadow-2xl bg-primary-400 aspect-[2/3]"></div>
                        @endfor
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Books Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Buku Populer</h2>
                <p class="text-gray-600 mt-1">Pilihan terbaik dengan rating tertinggi</p>
            </div>
            <a href="{{ route('books.index', ['sort' => 'rating']) }}"
               class="text-primary-600 hover:text-primary-700 font-medium flex items-center">
                Lihat Semua
                <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        @if($featuredCount > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($featuredBooks as $book)
                    @include('frontend.partials.book-card', ['book' => $book, 'showWishlist' => true])
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                Belum ada buku populer untuk ditampilkan.
            </div>
        @endif
    </div>
</section>

<!-- Categories Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Jelajahi Kategori</h2>
            <p class="text-gray-600 mt-2">Temukan buku sesuai minat Anda</p>
        </div>

        @if($categoryCount > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach($categories as $category)
                    @php
                        $slug = $get($category, 'slug');
                        $name = $get($category, 'name', 'Kategori');
                        $countBooks = $get($category, 'count', null);
                    @endphp

                    <a href="{{ $slug ? route('category', $slug) : route('books.index') }}"
                       class="bg-white rounded-xl p-6 text-center hover:shadow-lg transition-shadow border border-gray-100 group">
                        <div class="w-12 h-12 mx-auto bg-primary-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-primary-200 transition-colors">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>

                        <h3 class="font-semibold text-gray-900">{{ $name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ is_null($countBooks) ? '—' : $countBooks . ' buku' }}
                        </p>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                Belum ada kategori untuk ditampilkan.
            </div>
        @endif
    </div>
</section>

<!-- Newest Books Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Buku Terbaru</h2>
                <p class="text-gray-600 mt-1">Koleksi terbaru yang baru ditambahkan</p>
            </div>
            <a href="{{ route('books.index', ['sort' => 'newest']) }}"
               class="text-primary-600 hover:text-primary-700 font-medium flex items-center">
                Lihat Semua
                <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        @if($newestCount > 0)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($newestBooks as $book)
                    @include('frontend.partials.book-card', ['book' => $book, 'showWishlist' => true])
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                Belum ada buku terbaru untuk ditampilkan.
            </div>
        @endif
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-gradient-to-r from-primary-600 to-primary-700">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-white">Mulai Membaca Hari Ini</h2>
        <p class="mt-4 text-lg text-primary-100">
            Daftar sekarang dan dapatkan akses ke ribuan buku digital berkualitas
        </p>
        <div class="mt-8">
            @guest
                <a href="{{ route('register') }}"
                   class="inline-block bg-white text-primary-700 px-8 py-3 rounded-lg font-semibold hover:bg-primary-50 transition-colors">
                    Daftar Gratis Sekarang
                </a>
            @else
                <a href="{{ route('books.index') }}"
                   class="inline-block bg-white text-primary-700 px-8 py-3 rounded-lg font-semibold hover:bg-primary-50 transition-colors">
                    Jelajahi Buku
                </a>
            @endguest
        </div>
    </div>
</section>
@endsection
