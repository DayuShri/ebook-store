@extends('frontend.layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8">Keranjang Belanja</h1>

        @if($cart['item_count'] > 0)
            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Cart Items --}}
                <div class="lg:col-span-2 space-y-4">
                    @foreach($cart['items'] as $item)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex gap-4">
                            {{-- Selection Checkbox --}}
                            <div class="flex items-center pl-2">
                                <input type="checkbox"
                                    class="cart-item-checkbox w-5 h-5 text-primary-600 rounded border-gray-300 focus:ring-primary-500 cursor-pointer transition-colors"
                                    data-id="{{ $item['book_id'] }}" {{ ($item['is_selected'] ?? true) ? 'checked' : '' }}>
                            </div>

                            {{-- Book Cover --}}
                            <a href="{{ route('books.show', $item['book_id']) }}" class="flex-shrink-0">
                                <div class="w-20 h-28 rounded-lg overflow-hidden">
                                    @if(!empty($item['book']['cover_image_url']))
                                        <img src="{{ $item['book']['cover_image_url'] }}" alt="{{ $item['book']['title'] }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-primary-100 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </a>

                            {{-- Book Info --}}
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('books.show', $item['book_id']) }}"
                                    class="font-semibold text-gray-900 hover:text-primary-600 line-clamp-2">
                                    {{ $item['book']['title'] }}
                                </a>
                                <p class="text-sm text-gray-500 mt-1">
                                    @foreach($item['book']['authors'] ?? [] as $author)
                                        {{ $author['name'] }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </p>

                                {{-- Price --}}
                                <div class="mt-2">
                                    @if(!empty($item['book']['discount_percentage']) && $item['book']['discount_percentage'] > 0)
                                        <span class="font-bold text-primary-600">Rp
                                            {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                                        <span class="text-sm text-gray-400 line-through ml-2">Rp
                                            {{ number_format($item['book']['price'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="font-bold text-primary-600">Rp
                                            {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Remove Button --}}
                            <form action="{{ route('cart.remove', $item['book_id']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                {{-- Order Summary --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pesanan</h2>

                        {{-- Voucher Input --}}
                        <div class="mb-6">
                            @if($cart['voucher'])
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3 flex items-center justify-between">
                                    <div>
                                        <span class="text-green-700 font-medium">{{ $cart['voucher']['code'] }}</span>
                                        <p class="text-sm text-green-600">{{ $cart['voucher']['description'] }}</p>
                                    </div>
                                    <form action="{{ route('cart.voucher.remove') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-green-600 hover:text-green-800">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('cart.voucher') }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="code" placeholder="Kode Voucher"
                                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-primary-500 focus:border-primary-500">
                                    <button type="submit"
                                        class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors text-sm">
                                        Terapkan
                                    </button>
                                </form>
                            @endif

                            {{-- Available Vouchers Link --}}
                            <div class="mt-4">
                                <a href="{{ route('cart.voucher.select') }}"
                                    class="flex items-center justify-between w-full p-3 border border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-all group">
                                    <div class="flex items-center text-primary-700">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
                                            </path>
                                        </svg>
                                        <span class="font-medium text-sm">Lihat Voucher Tersedia</span>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-primary-500 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                        </path>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        {{-- Summary --}}
                        <div class="space-y-3 border-t border-gray-100 pt-4">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal (<span
                                        id="cart-selected-count">{{ $cart['selected_count'] ?? $cart['item_count'] }}</span>
                                    item)</span>
                                <span>Rp <span
                                        id="cart-subtotal">{{ number_format($cart['subtotal'], 0, ',', '.') }}</span></span>
                            </div>

                            <div id="cart-discount-row" class="flex justify-between text-green-600"
                                style="{{ $cart['discount'] > 0 ? '' : 'display:none;' }}">
                                <span>Diskon Voucher</span>
                                <span>-Rp <span
                                        id="cart-discount">{{ number_format($cart['discount'], 0, ',', '.') }}</span></span>
                            </div>

                            <div class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-100 pt-3">
                                <span>Total</span>
                                <span>Rp <span id="cart-total">{{ number_format($cart['total'], 0, ',', '.') }}</span></span>
                            </div>
                        </div>

                        {{-- Checkout Button --}}
                        <a href="{{ route('checkout') }}"
                            class="mt-6 block w-full bg-primary-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                            Lanjut ke Pembayaran
                        </a>

                        <a href="{{ route('books.index') }}"
                            class="mt-3 block w-full text-center text-gray-600 hover:text-gray-900 text-sm">
                            Lanjut Belanja
                        </a>
                    </div>
                </div>
            </div>
        @else
            {{-- Empty Cart --}}
            <div class="text-center py-16">
                <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
                <h3 class="mt-4 text-xl font-semibold text-gray-900">Keranjang Kosong</h3>
                <p class="mt-2 text-gray-500">Anda belum menambahkan buku apapun ke keranjang</p>
                <a href="{{ route('books.index') }}"
                    class="mt-6 inline-block bg-primary-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                    Jelajahi Katalog
                </a>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.cart-item-checkbox');
            const subtotalEl = document.getElementById('cart-subtotal');
            const totalEl = document.getElementById('cart-total');
            const discountEl = document.getElementById('cart-discount');
            const discountRow = document.getElementById('cart-discount-row');
            const countEl = document.getElementById('cart-selected-count');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const summaryContainer = document.querySelector('.lg\\:col-span-1 .bg-white'); // Target summary box

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const bookId = this.dataset.id;
                    const isSelected = this.checked;

                    // Add loading state
                    summaryContainer.classList.add('opacity-50', 'pointer-events-none');

                    fetch(`/cart/select/${bookId}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ is_selected: isSelected })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                subtotalEl.textContent = data.data.subtotal;
                                totalEl.textContent = data.data.total;
                                countEl.textContent = data.data.selected_count;

                                if (data.data.discount !== '0') {
                                    discountEl.textContent = data.data.discount;
                                    discountRow.style.display = 'flex';
                                } else {
                                    discountRow.style.display = 'none';
                                }
                            }
                        })
                        .catch(error => console.error('Error:', error))
                        .finally(() => {
                            // Remove loading state
                            summaryContainer.classList.remove('opacity-50', 'pointer-events-none');
                        });
                });
            });
        });
    </script>
@endpush