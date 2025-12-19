@extends('frontend.layouts.app')

@section('title', $book['title'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Breadcrumb --}}
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="{{ route('home') }}" class="hover:text-primary-600">Beranda</a></li>
            <li><span>/</span></li>
            <li><a href="{{ route('books.index') }}" class="hover:text-primary-600">Katalog</a></li>
            @if(!empty($book['categories']))
                <li><span>/</span></li>
                <li><a href="{{ route('category', $book['categories'][0]['slug']) }}" class="hover:text-primary-600">{{ $book['categories'][0]['name'] }}</a></li>
            @endif
            <li><span>/</span></li>
            <li class="text-gray-900 font-medium truncate max-w-xs">{{ $book['title'] }}</li>
        </ol>
    </nav>

    {{-- Book Detail Section --}}
    <div class="grid md:grid-cols-3 gap-8 mb-12">
        {{-- Cover Image --}}
        <div class="md:col-span-1">
            <div class="sticky top-24">
                <div class="aspect-[2/3] rounded-xl overflow-hidden shadow-lg">
                    @if(!empty($book['cover_image_url']))
                        <img src="{{ $book['cover_image_url'] }}" alt="{{ $book['title'] }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                            <svg class="w-24 h-24 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                
                {{-- Discount Badge --}}
                @if(!empty($book['discount_percentage']) && $book['discount_percentage'] > 0)
                    <div class="absolute top-4 left-4 bg-red-500 text-white text-sm font-bold px-3 py-1 rounded-lg">
                        -{{ number_format($book['discount_percentage'], 0) }}%
                    </div>
                @endif
            </div>
        </div>

        {{-- Book Info --}}
        <div class="md:col-span-2">
            {{-- Categories --}}
            @if(!empty($book['categories']))
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach($book['categories'] as $category)
                        <a href="{{ route('category', $category['slug']) }}" class="text-sm bg-primary-50 text-primary-700 px-3 py-1 rounded-full hover:bg-primary-100">
                            {{ $category['name'] }}
                        </a>
                    @endforeach
                </div>
            @endif

            <h1 class="text-3xl font-bold text-gray-900">{{ $book['title'] }}</h1>
            
            @if(!empty($book['subtitle']))
                <p class="text-lg text-gray-600 mt-2">{{ $book['subtitle'] }}</p>
            @endif

            {{-- Author(s) --}}
            <div class="mt-4">
                <span class="text-gray-500">oleh</span>
                @foreach($book['authors'] ?? [] as $index => $author)
                    <span class="text-primary-600 font-medium">{{ $author['name'] }}</span>{{ $index < count($book['authors']) - 1 ? ', ' : '' }}
                @endforeach
            </div>

            {{-- Rating --}}
            @if(isset($book['avg_rating']))
                <div class="flex items-center mt-4">
                    <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= floor($book['avg_rating']) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <span class="ml-2 text-lg font-semibold">{{ number_format($book['avg_rating'], 1) }}</span>
                    <span class="ml-2 text-gray-500">({{ $book['review_count'] ?? 0 }} ulasan)</span>
                </div>
            @endif

            {{-- Price --}}
            <div class="mt-6 p-6 bg-gray-50 rounded-xl">
                <div class="flex items-baseline gap-3">
                    @if(!empty($book['discount_percentage']) && $book['discount_percentage'] > 0)
                        @php
                            $discountedPrice = $book['price'] * (1 - $book['discount_percentage'] / 100);
                        @endphp
                        <span class="text-3xl font-bold text-primary-600">Rp {{ number_format($discountedPrice, 0, ',', '.') }}</span>
                        <span class="text-xl text-gray-400 line-through">Rp {{ number_format($book['price'], 0, ',', '.') }}</span>
                    @else
                        <span class="text-3xl font-bold text-primary-600">Rp {{ number_format($book['price'], 0, ',', '.') }}</span>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="mt-6 flex flex-wrap gap-3">
                    <form action="{{ route('cart.add', $book['id']) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-primary-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-primary-700 transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Tambah ke Keranjang
                        </button>
                    </form>
                    
                    @auth
                        <form action="{{ route($isInWishlist ? 'wishlist.remove' : 'wishlist.add', $book['id']) }}" method="POST">
                            @csrf
                            @if($isInWishlist)
                                @method('DELETE')
                            @endif
                            <button type="submit" class="p-3 border-2 {{ $isInWishlist ? 'border-red-500 text-red-500' : 'border-gray-300 text-gray-600' }} rounded-lg hover:bg-gray-50 transition-colors">
                                <svg class="w-6 h-6" fill="{{ $isInWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                        </form>
                    @endauth
                </div>
            </div>

            {{-- Book Metadata --}}
            <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
                @if(!empty($book['publisher']))
                    <div class="bg-white p-4 rounded-lg border border-gray-100">
                        <span class="text-sm text-gray-500">Penerbit</span>
                        <p class="font-medium text-gray-900">{{ $book['publisher']['name'] }}</p>
                    </div>
                @endif
                @if(!empty($book['publication_date']))
                    <div class="bg-white p-4 rounded-lg border border-gray-100">
                        <span class="text-sm text-gray-500">Terbit</span>
                        <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($book['publication_date'])->format('d M Y') }}</p>
                    </div>
                @endif
                @if(!empty($book['page_count']))
                    <div class="bg-white p-4 rounded-lg border border-gray-100">
                        <span class="text-sm text-gray-500">Halaman</span>
                        <p class="font-medium text-gray-900">{{ $book['page_count'] }}</p>
                    </div>
                @endif
                @if(!empty($book['language']))
                    <div class="bg-white p-4 rounded-lg border border-gray-100">
                        <span class="text-sm text-gray-500">Bahasa</span>
                        <p class="font-medium text-gray-900">{{ $book['language'] === 'id' ? 'Indonesia' : $book['language'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabs: Synopsis & Reviews --}}
    <div class="border-t border-gray-200 pt-8">
        <div x-data="{ activeTab: 'synopsis' }">
            {{-- Tab Headers --}}
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8">
                    <button @click="activeTab = 'synopsis'" 
                            :class="activeTab === 'synopsis' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Sinopsis
                    </button>
                    <button @click="activeTab = 'reviews'" 
                            :class="activeTab === 'reviews' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Ulasan ({{ $reviewStats['total'] ?? 0 }})
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="py-8">
                {{-- Synopsis --}}
                <div x-show="activeTab === 'synopsis'">
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $book['synopsis'] ?? 'Tidak ada sinopsis tersedia.' }}</p>
                    </div>
                </div>

                {{-- Reviews --}}
                <div x-show="activeTab === 'reviews'" x-cloak>
                    @if(count($reviews) > 0)
                        <div class="space-y-6">
                            @foreach($reviews as $review)
                                <div class="bg-white p-6 rounded-xl border border-gray-100">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <span class="font-semibold text-gray-900">{{ $review['user_name'] }}</span>
                                                @if($review['is_verified_purchase'] ?? false)
                                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Pembelian Terverifikasi</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center mt-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-4 h-4 {{ $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                                <span class="ml-2 text-sm text-gray-500">{{ \Carbon\Carbon::parse($review['created_at'])->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-4 text-gray-700">{{ $review['review_text'] }}</p>
                                    <div class="mt-4 flex items-center text-sm text-gray-500">
                                        <span>{{ $review['helpful_count'] ?? 0 }} orang menganggap ini membantu</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500">Belum ada ulasan untuk buku ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Related Books --}}
    @if(count($relatedBooks) > 0)
        <div class="border-t border-gray-200 pt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Buku Terkait</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($relatedBooks as $relatedBook)
                    @include('frontend.partials.book-card', ['book' => $relatedBook])
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
