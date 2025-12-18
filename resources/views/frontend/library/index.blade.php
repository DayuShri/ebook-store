@extends('frontend.layouts.app')

@section('title', 'Perpustakaan Saya')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8">Perpustakaan Saya</h1>

    @if(count($libraryItems) > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($libraryItems as $item)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group">
                    {{-- Book Cover --}}
                    <div class="relative aspect-[2/3] overflow-hidden">
                        @if(!empty($item['book']['cover_image_url']))
                            <img src="{{ $item['book']['cover_image_url'] }}" alt="{{ $item['book']['title'] }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                                <svg class="w-16 h-16 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"></path>
                                </svg>
                            </div>
                        @endif

                        {{-- Reading Progress Overlay --}}
                        @if($item['reading_progress'] > 0)
                            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200">
                                <div class="h-full bg-primary-500" style="width: {{ $item['reading_progress'] }}%"></div>
                            </div>
                        @endif

                        {{-- Completed Badge --}}
                        @if($item['reading_progress'] >= 100)
                            <div class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded">
                                Selesai
                            </div>
                        @endif
                    </div>

                    {{-- Book Info --}}
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 line-clamp-2">{{ $item['book']['title'] }}</h3>
                        @if(!empty($item['book']['authors']))
                            <p class="text-sm text-gray-500 mt-1 line-clamp-1">
                                {{ $item['book']['authors'][0]['name'] ?? '' }}
                            </p>
                        @endif

                        {{-- Progress Text --}}
                        <div class="mt-2 text-sm text-gray-500">
                            @if($item['reading_progress'] >= 100)
                                <span class="text-green-600">Selesai dibaca</span>
                            @elseif($item['reading_progress'] > 0)
                                <span>{{ number_format($item['reading_progress'], 0) }}% selesai</span>
                            @else
                                <span>Belum dibaca</span>
                            @endif
                        </div>

                        {{-- Read Button (NO Download) --}}
                        <a href="{{ route('library.read', $item['book_id']) }}" 
                           class="mt-4 block w-full bg-primary-600 text-white text-center py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                            {{ $item['reading_progress'] > 0 ? 'Lanjut Baca' : 'Baca Sekarang' }}
                        </a>
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
