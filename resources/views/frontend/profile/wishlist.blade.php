@extends('frontend.layouts.app')

@section('title', 'Wishlist Saya')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8">Wishlist Saya</h1>

    @if(count($wishlistItems) > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($wishlistItems as $book)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group relative">
                    {{-- Remove from Wishlist --}}
                    <form action="{{ route('wishlist.remove', $book['id']) }}" method="POST" class="absolute top-2 right-2 z-10">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 bg-white/90 rounded-full shadow-md hover:bg-white transition-colors"
                                title="Hapus dari wishlist">
                            <svg class="w-5 h-5 text-red-500" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </button>
                    </form>

                    {{-- Book Cover --}}
                    <a href="{{ route('books.show', $book['id']) }}" class="block relative aspect-[2/3] overflow-hidden">
                        @if(!empty($book['cover_image_url']))
                            <img src="{{ $book['cover_image_url'] }}" alt="{{ $book['title'] }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                                <svg class="w-16 h-16 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13"></path>
                                </svg>
                            </div>
                        @endif
                    </a>

                    {{-- Book Info --}}
                    <div class="p-4">
                        <a href="{{ route('books.show', $book['id']) }}" class="font-semibold text-gray-900 hover:text-primary-600 line-clamp-2">
                            {{ $book['title'] }}
                        </a>
                        @if(!empty($book['authors']))
                            <p class="text-sm text-gray-500 mt-1">{{ $book['authors'][0]['name'] ?? '' }}</p>
                        @endif
                        
                        {{-- Price --}}
                        <div class="mt-2">
                            @if(!empty($book['discount_percentage']) && $book['discount_percentage'] > 0)
                                @php $discountedPrice = $book['price'] * (1 - $book['discount_percentage'] / 100); @endphp
                                <span class="font-bold text-primary-600">Rp {{ number_format($discountedPrice, 0, ',', '.') }}</span>
                                <span class="text-sm text-gray-400 line-through ml-1">Rp {{ number_format($book['price'], 0, ',', '.') }}</span>
                            @else
                                <span class="font-bold text-primary-600">Rp {{ number_format($book['price'], 0, ',', '.') }}</span>
                            @endif
                        </div>

                        {{-- Add to Cart --}}
                        <form action="{{ route('cart.add', $book['id']) }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="w-full bg-primary-600 text-white py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors text-sm">
                                + Keranjang
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty Wishlist --}}
        <div class="text-center py-16">
            <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
            <h3 class="mt-4 text-xl font-semibold text-gray-900">Wishlist Kosong</h3>
            <p class="mt-2 text-gray-500">Anda belum menambahkan buku ke wishlist</p>
            <a href="{{ route('books.index') }}" class="mt-6 inline-block bg-primary-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                Jelajahi Katalog
            </a>
        </div>
    @endif
</div>
@endsection
