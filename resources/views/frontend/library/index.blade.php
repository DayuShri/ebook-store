@extends('frontend.layouts.app')

@section('title', 'Perpustakaan Saya')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8">Perpustakaan Saya</h1>

    @if(isset($error))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p class="text-red-600">{{ $error }}</p>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-green-600">{{ session('success') }}</p>
        </div>
    @endif

    @if(count($libraryItems) > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($libraryItems as $item)
                @php
                    $book = $item['book'];
                    $progress = $item['progress'];
                    $progressPercentage = $progress ? $progress['progress_percentage'] : 0;
                @endphp
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group">
                    {{-- Book Cover --}}
                    <div class="relative aspect-[2/3] overflow-hidden">
                        @if(!empty($book['cover_image_url']))
                            <img src="{{ $book['cover_image_url'] }}" alt="{{ $book['title'] }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                                <svg class="w-16 h-16 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"></path>
                                </svg>
                            </div>
                        @endif

                        {{-- Reading Progress Bar (Always visible) --}}
                        <div class="absolute bottom-0 left-0 right-0 h-2 bg-gray-300">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-primary-600 transition-all duration-300" 
                                 style="width: {{ $progressPercentage }}%"></div>
                        </div>

                        {{-- Progress Badge --}}
                        @if($progressPercentage >= 100)
                            <div class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                ✓ Selesai
                            </div>
                        @elseif($progressPercentage > 0)
                            <div class="absolute top-2 right-2 bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                {{ number_format($progressPercentage, 0) }}%
                            </div>
                        @endif
                    </div>

                    {{-- Book Info --}}
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 line-clamp-2">{{ $book['title'] ?? 'Untitled' }}</h3>
                        @if(!empty($book['authors']))
                            <p class="text-sm text-gray-500 mt-1 line-clamp-1">
                                {{ is_array($book['authors']) ? ($book['authors'][0]['name'] ?? '') : $book['authors'] }}
                            </p>
                        @endif

                        {{-- Progress Text & Bar --}}
                        <div class="mt-3">
                            <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                @if($progressPercentage >= 100)
                                    <span class="font-medium text-green-600">✓ Selesai dibaca</span>
                                @elseif($progressPercentage > 0)
                                    <span class="font-medium text-blue-600">Sedang dibaca</span>
                                    <span class="text-gray-500">{{ number_format($progressPercentage, 0) }}%</span>
                                @else
                                    <span class="text-gray-500">Belum dibaca</span>
                                    <span class="text-gray-400">0%</span>
                                @endif
                            </div>
                            
                            {{-- Progress Bar Detail --}}
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 ease-out
                                    @if($progressPercentage >= 100) bg-green-500
                                    @elseif($progressPercentage > 0) bg-gradient-to-r from-blue-500 to-primary-600
                                    @else bg-gray-300
                                    @endif" 
                                    style="width: {{ $progressPercentage }}%">
                                </div>
                            </div>
                            
                            @if($progress && $progress['last_read_at'])
                                <p class="text-xs text-gray-400 mt-1">
                                    Terakhir dibaca {{ \Carbon\Carbon::parse($progress['last_read_at'])->diffForHumans() }}
                                </p>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="mt-4 space-y-2">
                            <a href="{{ route('library.read', $item['library_item']->book_id) }}" 
                               class="block w-full bg-primary-600 text-white text-center py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                                {{ $progressPercentage > 0 ? 'Lanjut Baca' : 'Baca Sekarang' }}
                            </a>
                            
                            <a href="{{ route('library.reviews', $item['library_item']->book_id) }}" 
                               class="block w-full bg-gray-100 text-gray-700 text-center py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                Lihat Review
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty Library --}}
        <div class="text-center py-16">
            <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <h3 class="mt-4 text-xl font-semibold text-gray-900">Perpustakaan Kosong</h3>
            <p class="mt-2 text-gray-500">Anda belum memiliki buku. Beli buku untuk mulai membaca.</p>
            <a href="{{ route('books.index') }}" class="mt-6 inline-block bg-primary-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                Jelajahi Katalog
            </a>
        </div>
    @endif
</div>
@endsection
