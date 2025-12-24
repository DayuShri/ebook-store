@extends('frontend.layouts.app')

@section('title', 'Pilih Voucher')

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center mb-6">
            <a href="{{ request('source') === 'checkout' ? route('checkout') : route('cart.index') }}"
                class="mr-4 p-2 rounded-full hover:bg-gray-100 transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Pilih Voucher</h1>
        </div>

        {{-- Voucher List --}}
        <div class="space-y-4">
            @forelse($vouchers as $voucher)
                @php
                    $minPurchaseMet = $cart['subtotal'] >= $voucher->min_purchase_amount;
                    $isApplied = $cart['voucher'] && $cart['voucher']['code'] === $voucher->code;
                @endphp

                <div
                    class="bg-white rounded-xl shadow-sm border overflow-hidden transition-all {{ $isApplied ? 'border-primary-500 ring-1 ring-primary-500' : ($minPurchaseMet ? 'border-gray-200 hover:border-primary-200 hover:shadow-md' : 'border-gray-100 opacity-60 bg-gray-50') }}">
                    <div class="p-4 sm:p-5 flex items-start gap-4">
                        {{-- Icon / Graphic --}}
                        <div class="flex-shrink-0">
                            <div
                                class="w-12 h-12 rounded-lg flex items-center justify-center {{ $minPurchaseMet ? 'bg-primary-100 text-primary-600' : 'bg-gray-100 text-gray-400' }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
                                    </path>
                                </svg>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="font-bold text-gray-900 text-lg">{{ $voucher->code }}</h3>
                                @if($isApplied)
                                    <span
                                        class="bg-primary-100 text-primary-700 text-xs font-bold px-2 py-1 rounded-full uppercase">Terpakai</span>
                                @endif
                            </div>
                            <p class="text-gray-600 text-sm mb-2">{{ $voucher->description }}</p>

                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                <span class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Berlaku s/d {{ $voucher->valid_until->format('d M Y') }}
                                </span>
                                @if($voucher->min_purchase_amount > 0)
                                    <span class="flex items-center {{ !$minPurchaseMet ? 'text-red-500 font-medium' : '' }}">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Min. Belanja Rp {{ number_format($voucher->min_purchase_amount, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="flex-shrink-0 self-center">
                            @if($isApplied)
                                <form action="{{ route('cart.voucher.remove') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 transition-colors">
                                        Batalkan
                                    </button>
                                </form>
                            @elseif($minPurchaseMet)
                                <form action="{{ route('cart.voucher') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="code" value="{{ $voucher->code }}">
                                    <button type="submit"
                                        class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors shadow-sm">
                                        Pakai
                                    </button>
                                </form>
                            @else
                                <button disabled
                                    class="px-4 py-2 bg-gray-100 text-gray-400 rounded-lg text-sm font-medium cursor-not-allowed">
                                    Pakai
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
                        </path>
                    </svg>
                    <p class="text-gray-500 font-medium">Belum ada voucher yang tersedia saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection