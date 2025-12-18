{{-- Sidebar Filters Component --}}
{{-- Usage: @include('frontend.partials.sidebar-filters', ['categories' => $categories]) --}}

<aside class="w-full lg:w-64 flex-shrink-0">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
        <h3 class="font-semibold text-gray-900 mb-4">Filter</h3>
        
        <form action="{{ route('books.index') }}" method="GET" id="filter-form">
            {{-- Preserve search query --}}
            @if(request('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif
            
            {{-- Categories --}}
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Kategori</h4>
                <div class="space-y-2">
                    @foreach($categories ?? [] as $category)
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="categories[]" value="{{ $category['slug'] }}"
                                   {{ in_array($category['slug'], request('categories', [])) ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <span class="ml-2 text-sm text-gray-600">{{ $category['name'] }}</span>
                            @if(isset($category['count']))
                                <span class="ml-auto text-xs text-gray-400">({{ $category['count'] }})</span>
                            @endif
                        </label>
                        
                        {{-- Child categories --}}
                        @if(!empty($category['children']))
                            <div class="ml-6 space-y-2">
                                @foreach($category['children'] as $child)
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="categories[]" value="{{ $child['slug'] }}"
                                               {{ in_array($child['slug'], request('categories', [])) ? 'checked' : '' }}
                                               class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                        <span class="ml-2 text-sm text-gray-600">{{ $child['name'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            
            {{-- Price Range --}}
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Rentang Harga</h4>
                <div class="flex items-center space-x-2">
                    <input type="number" name="price_min" placeholder="Min" 
                           value="{{ request('price_min') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                    <span class="text-gray-400">-</span>
                    <input type="number" name="price_max" placeholder="Max"
                           value="{{ request('price_max') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
            
            {{-- Rating --}}
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Rating Minimum</h4>
                <div class="space-y-2">
                    @foreach([4, 3, 2, 1] as $rating)
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="min_rating" value="{{ $rating }}"
                                   {{ request('min_rating') == $rating ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                                <span class="ml-1 text-sm text-gray-500">ke atas</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
            
            {{-- Apply/Clear Buttons --}}
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-primary-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-primary-700 transition-colors text-sm">
                    Terapkan
                </button>
                <a href="{{ route('books.index') }}" class="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors text-sm text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>
</aside>
