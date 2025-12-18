{{-- Book Card Component --}}
{{-- Usage: @include('frontend.partials.book-card', ['book' => $book]) --}}
@props(['book', 'showWishlist' => true])

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group hover:shadow-lg transition-shadow duration-300">
    <!-- Cover Image -->
    <a href="{{ route('books.show', $book['id']) }}" class="block relative aspect-[2/3] overflow-hidden">
        @if(!empty($book['cover_image_url']))
            <img src="{{ $book['cover_image_url'] }}" alt="{{ $book['title'] }}" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                <svg class="w-16 h-16 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
        @endif
        
        {{-- Discount Badge --}}
        @if(!empty($book['discount_percentage']) && $book['discount_percentage'] > 0)
            <div class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                -{{ number_format($book['discount_percentage'], 0) }}%
            </div>
        @endif
        
        {{-- Wishlist Button --}}
        @if($showWishlist)
            @auth
                <button type="button" 
                        onclick="toggleWishlist('{{ $book['id'] }}')"
                        class="absolute top-2 right-2 p-2 bg-white/90 rounded-full shadow-md hover:bg-white transition-colors opacity-0 group-hover:opacity-100"
                        data-wishlist-btn="{{ $book['id'] }}">
                    <svg class="w-5 h-5 text-gray-600 hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </button>
            @endauth
        @endif
    </a>
    
    <!-- Details -->
    <div class="p-4">
        {{-- Category --}}
        @if(!empty($book['categories']) && count($book['categories']) > 0)
            <a href="{{ route('books.index', ['category' => $book['categories'][0]['slug']]) }}" 
               class="text-xs text-primary-600 font-medium hover:text-primary-700">
                {{ $book['categories'][0]['name'] }}
            </a>
        @endif
        
        {{-- Title --}}
        <h3 class="mt-1">
            <a href="{{ route('books.show', $book['id']) }}" class="font-semibold text-gray-900 hover:text-primary-600 line-clamp-2">
                {{ $book['title'] }}
            </a>
        </h3>
        
        {{-- Author --}}
        @if(!empty($book['authors']))
            <p class="mt-1 text-sm text-gray-500 line-clamp-1">
                @foreach($book['authors'] as $index => $author)
                    {{ $author['name'] }}{{ $index < count($book['authors']) - 1 ? ', ' : '' }}
                @endforeach
            </p>
        @elseif(!empty($book['author_name']))
            <p class="mt-1 text-sm text-gray-500 line-clamp-1">{{ $book['author_name'] }}</p>
        @endif
        
        {{-- Rating --}}
        @if(isset($book['avg_rating']))
            <div class="flex items-center mt-2">
                <div class="flex items-center">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($book['avg_rating']))
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endif
                    @endfor
                </div>
                <span class="ml-1 text-sm text-gray-500">{{ number_format($book['avg_rating'], 1) }}</span>
            </div>
        @endif
        
        {{-- Price --}}
        <div class="mt-3 flex items-center justify-between">
            <div>
                @if(!empty($book['discount_percentage']) && $book['discount_percentage'] > 0)
                    @php
                        $discountedPrice = $book['price'] * (1 - $book['discount_percentage'] / 100);
                    @endphp
                    <span class="text-lg font-bold text-primary-600">Rp {{ number_format($discountedPrice, 0, ',', '.') }}</span>
                    <span class="text-sm text-gray-400 line-through ml-1">Rp {{ number_format($book['price'], 0, ',', '.') }}</span>
                @else
                    <span class="text-lg font-bold text-primary-600">Rp {{ number_format($book['price'], 0, ',', '.') }}</span>
                @endif
            </div>
        </div>
        
        {{-- Add to Cart Button --}}
        <form action="{{ route('cart.add', $book['id']) }}" method="POST" class="mt-3">
            @csrf
            <button type="submit" class="w-full bg-primary-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-primary-700 transition-colors text-sm">
                + Keranjang
            </button>
        </form>
    </div>
</div>
