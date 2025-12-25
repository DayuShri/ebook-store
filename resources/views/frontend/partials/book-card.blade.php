{{-- Book Card Partial --}}
{{-- Usage: @include('frontend.partials.book-card', ['book' => $book, 'showWishlist' => true]) --}}

@php
    // fallback param
    $showWishlist = $showWishlist ?? true;

    // helper aman array/object
    $get = function ($item, string $key, $default = null) {
        if (is_array($item)) return $item[$key] ?? $default;
        if (is_object($item)) return $item->{$key} ?? $default;
        return $default;
    };

    // Normalize basic fields
    $bookId   = $get($book, 'id');
    $title    = $get($book, 'title', 'Buku');
    $cover    = $get($book, 'cover_image_url');
    $price    = (float) ($get($book, 'price', 0) ?? 0);
    $discount = (float) ($get($book, 'discount_percentage', 0) ?? 0);
    $avgRating = $get($book, 'avg_rating', null);

    // Categories normalize (array/collection)
    $categories = $get($book, 'categories', []);
    if (is_object($categories) && method_exists($categories, 'toArray')) {
        $categories = $categories->toArray();
    }
    if (!is_array($categories)) $categories = [];

    $firstCategory = $categories[0] ?? null;
    $catSlug = $firstCategory ? $get($firstCategory, 'slug') : null;
    $catName = $firstCategory ? $get($firstCategory, 'name') : null;

    // Authors normalize (optional)
    $authors = $get($book, 'authors', []);
    if (is_object($authors) && method_exists($authors, 'toArray')) {
        $authors = $authors->toArray();
    }
    if (!is_array($authors)) $authors = [];

    $authorNameFallback = $get($book, 'author_name', null);

    // Price calc
    $hasDiscount = $discount > 0;
    $discountedPrice = $hasDiscount ? ($price * (1 - ($discount / 100))) : $price;
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group hover:shadow-lg transition-shadow duration-300">

    <!-- Cover -->
    <a href="{{ $bookId ? route('books.show', $bookId) : route('books.index') }}"
       class="block relative aspect-[2/3] overflow-hidden">

        @if(!empty($cover))
            <img src="{{ $cover }}"
                 alt="{{ $title }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                <svg class="w-16 h-16 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
        @endif

        {{-- Discount Badge --}}
        @if($hasDiscount)
            <div class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                -{{ number_format($discount, 0) }}%
            </div>
        @endif

        {{-- Wishlist Button (opsional, kamu bisa aktifin nanti kalau sudah ada JS + endpoint) --}}
        @if($showWishlist)
            @auth
                <button type="button"
                        onclick="window.toggleWishlist && toggleWishlist('{{ $bookId }}')"
                        class="absolute top-2 right-2 p-2 bg-white/90 rounded-full shadow-md hover:bg-white transition-colors opacity-0 group-hover:opacity-100"
                        data-wishlist-btn="{{ $bookId }}">
                    <svg class="w-5 h-5 text-gray-600 hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </button>
            @endauth
        @endif
    </a>

    <!-- Detail -->
    <div class="p-4">

        {{-- Category --}}
        @if(!empty($catSlug) && !empty($catName))
            <a href="{{ route('books.index', ['category' => $catSlug]) }}"
               class="text-xs text-primary-600 font-medium hover:text-primary-700">
                {{ $catName }}
            </a>
        @endif

        {{-- Title --}}
        <h3 class="mt-1">
            <a href="{{ $bookId ? route('books.show', $bookId) : route('books.index') }}"
               class="font-semibold text-gray-900 hover:text-primary-600 line-clamp-2">
                {{ $title }}
            </a>
        </h3>

        {{-- Author --}}
        @if(!empty($authors))
            <p class="mt-1 text-sm text-gray-500 line-clamp-1">
                @foreach($authors as $index => $author)
                    {{ $get($author, 'name', '-') }}{{ $index < count($authors) - 1 ? ', ' : '' }}
                @endforeach
            </p>
        @elseif(!empty($authorNameFallback))
            <p class="mt-1 text-sm text-gray-500 line-clamp-1">{{ $authorNameFallback }}</p>
        @endif

        {{-- Rating --}}
        @if(!is_null($avgRating))
            @php $avgRatingFloat = (float) $avgRating; @endphp
            <div class="flex items-center mt-2">
                <div class="flex items-center">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= floor($avgRatingFloat) ? 'text-yellow-400' : 'text-gray-300' }}"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <span class="ml-1 text-sm text-gray-500">{{ number_format($avgRatingFloat, 1) }}</span>
            </div>
        @endif

        {{-- Price --}}
        <div class="mt-3 flex items-center justify-between">
            <div>
                @if($hasDiscount)
                    <span class="text-lg font-bold text-primary-600">
                        Rp {{ number_format($discountedPrice, 0, ',', '.') }}
                    </span>
                    <span class="text-sm text-gray-400 line-through ml-1">
                        Rp {{ number_format($price, 0, ',', '.') }}
                    </span>
                @else
                    <span class="text-lg font-bold text-primary-600">
                        Rp {{ number_format($price, 0, ',', '.') }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Add to Cart --}}
        @if(!empty($bookId))
            <form action="{{ route('cart.add', $bookId) }}" method="POST" class="mt-3">
                @csrf
                <button type="submit"
                        class="w-full bg-primary-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-primary-700 transition-colors text-sm">
                    + Keranjang
                </button>
            </form>
        @else
            <div class="mt-3 text-center text-sm text-gray-500">
                Buku tidak valid
            </div>
        @endif

    </div>
</div>
