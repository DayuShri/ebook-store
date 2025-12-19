@extends('frontend.layouts.app')

@section('title', 'Review: ' . ($book['title'] ?? 'Buku'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('library.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Perpustakaan
        </a>
        
        <div class="flex items-start space-x-6">
            {{-- Book Cover --}}
            <div class="flex-shrink-0">
                @if(!empty($book['cover_image_url']))
                    <img src="{{ $book['cover_image_url'] }}" alt="{{ $book['title'] }}" 
                         class="w-40 h-60 object-cover rounded-lg shadow-lg">
                @else
                    <div class="w-40 h-60 bg-gradient-to-br from-primary-100 to-primary-200 rounded-lg shadow-lg flex items-center justify-center">
                        <svg class="w-16 h-16 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"></path>
                        </svg>
                    </div>
                @endif
            </div>
            
            {{-- Book Info --}}
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $book['title'] ?? 'Untitled' }}</h1>
                @if(!empty($book['authors']))
                    <p class="text-lg text-gray-600 mb-4">
                        {{ is_array($book['authors']) ? ($book['authors'][0]['name'] ?? '') : $book['authors'] }}
                    </p>
                @endif
                
                @if(!empty($book['description']))
                    <p class="text-gray-700 mb-4 line-clamp-3">{{ $book['description'] }}</p>
                @endif
                
                @if($hasAccess)
                    <a href="{{ route('library.read', $bookId) }}" 
                       class="inline-block bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                        Baca Buku
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-green-600">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-red-600">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Review Summary --}}
    @if($reviews->count() > 0)
        @php
            $avgRating = round($reviews->avg('rating'), 1);
            $ratingCounts = $reviews->countBy('rating');
        @endphp
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-center space-x-8">
                <div class="text-center">
                    <div class="text-5xl font-bold text-gray-900">{{ number_format($avgRating, 1) }}</div>
                    <div class="flex items-center justify-center mt-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= floor($avgRating) ? 'text-yellow-400' : 'text-gray-300' }}" 
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <div class="text-sm text-gray-500 mt-1">{{ $reviews->count() }} review</div>
                </div>
                
                <div class="flex-1">
                    @foreach([5,4,3,2,1] as $star)
                        @php
                            $count = $ratingCounts->get($star, 0);
                            $percentage = $reviews->count() > 0 ? ($count / $reviews->count()) * 100 : 0;
                        @endphp
                        <div class="flex items-center mb-2">
                            <div class="w-12 text-sm text-gray-600">{{ $star }} ★</div>
                            <div class="flex-1 mx-3 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-yellow-400" style="width: {{ $percentage }}%"></div>
                            </div>
                            <div class="w-12 text-sm text-gray-600 text-right">{{ $count }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Write Review Section --}}
    @if($hasAccess)
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                {{ $userReview ? 'Edit Review Anda' : 'Tulis Review' }}
            </h2>
            
            <form action="{{ route('library.reviews.store', $bookId) }}" method="POST">
                @csrf
                
                {{-- Rating --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                    <div class="flex space-x-2" id="rating-stars">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button" data-rating="{{ $i }}" 
                                    class="rating-star text-3xl {{ $userReview && $userReview->rating >= $i ? 'text-yellow-400' : 'text-gray-300' }} hover:text-yellow-400 transition-colors">
                                ★
                            </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="rating-input" value="{{ $userReview->rating ?? 0 }}" required>
                    @error('rating')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Review Text --}}
                <div class="mb-4">
                    <label for="review_text" class="block text-sm font-medium text-gray-700 mb-2">
                        Review Anda (opsional)
                    </label>
                    <textarea name="review_text" id="review_text" rows="4" 
                              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary-500 focus:border-primary-500"
                              placeholder="Bagikan pendapat Anda tentang buku ini...">{{ old('review_text', $userReview->review_text ?? '') }}</textarea>
                    @error('review_text')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                    {{ $userReview ? 'Update Review' : 'Kirim Review' }}
                </button>
            </form>
        </div>
    @else
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-blue-700">Anda harus memiliki buku ini untuk memberikan review.</p>
        </div>
    @endif

    {{-- Reviews List --}}
    <div class="space-y-4">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">
            Review dari Pembaca ({{ $reviews->count() }})
        </h2>
        
        @forelse($reviews as $review)
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex items-start space-x-4">
                    {{-- User Avatar --}}
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                            <span class="text-primary-600 font-semibold text-lg">
                                {{ strtoupper(substr($review->user->email ?? 'U', 0, 1)) }}
                            </span>
                        </div>
                    </div>
                    
                    {{-- Review Content --}}
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $review->user->profile->full_name ?? $review->user->email ?? 'Anonymous' }}</p>
                                <div class="flex items-center mt-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" 
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    <span class="ml-2 text-sm text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            
                            @if($review->is_verified_purchase)
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">
                                    ✓ Verified Purchase
                                </span>
                            @endif
                        </div>
                        
                        @if($review->review_text)
                            <p class="text-gray-700 mt-2">{{ $review->review_text }}</p>
                        @endif
                        
                        {{-- Helpful Count --}}
                        <div class="mt-3 flex items-center space-x-4 text-sm text-gray-500">
                            <span>{{ $review->helpful_count }} orang merasa review ini membantu</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-gray-50 rounded-lg p-8 text-center">
                <p class="text-gray-500">Belum ada review untuk buku ini.</p>
                @if($hasAccess)
                    <p class="text-gray-500 mt-2">Jadilah yang pertama memberikan review!</p>
                @endif
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('rating-input');
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.remove('text-gray-300');
                    s.classList.add('text-yellow-400');
                } else {
                    s.classList.remove('text-yellow-400');
                    s.classList.add('text-gray-300');
                }
            });
        });
    });
});
</script>
@endpush
@endsection
