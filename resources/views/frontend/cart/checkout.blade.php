@extends('frontend.layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

    <div class="grid md:grid-cols-2 gap-8">
        {{-- Order Summary --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Detail Pesanan</h2>
            
            <div class="space-y-4">
                @foreach($cart['items'] as $item)
                    <div class="flex gap-3">
                        <div class="w-12 h-16 rounded overflow-hidden flex-shrink-0">
                            @if(!empty($item['book']['cover_image_url']))
                                <img src="{{ $item['book']['cover_image_url'] }}" alt="{{ $item['book']['title'] }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 line-clamp-1">{{ $item['book']['title'] }}</p>
                            <p class="text-sm text-gray-500">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-gray-100 mt-6 pt-4 space-y-2">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($cart['subtotal'], 0, ',', '.') }}</span>
                </div>
                @if($cart['discount'] > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Diskon</span>
                        <span>-Rp {{ number_format($cart['discount'], 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-100 pt-2">
                    <span>Total</span>
                    <span>Rp {{ number_format($cart['total'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Payment Method --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Metode Pembayaran</h2>

            {{-- Wallet Balance --}}
            <div class="p-4 rounded-xl {{ $canPay ? 'bg-primary-50 border-2 border-primary-500' : 'bg-red-50 border-2 border-red-300' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 {{ $canPay ? 'text-primary-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold {{ $canPay ? 'text-gray-900' : 'text-red-900' }}">Saldo Wallet</p>
                            <p class="text-2xl font-bold {{ $canPay ? 'text-primary-600' : 'text-red-600' }}">
                                Rp {{ number_format($walletBalance, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    @if($canPay)
                        <svg class="w-6 h-6 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </div>
            </div>

            @if(!$canPay)
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-yellow-800">Saldo tidak mencukupi</p>
                            <p class="text-sm text-yellow-700 mt-1">
                                Anda membutuhkan Rp {{ number_format($cart['total'] - $walletBalance, 0, ',', '.') }} lagi untuk menyelesaikan pembayaran.
                            </p>
                            <a href="{{ route('wallet.topup') }}" class="inline-block mt-3 text-sm bg-yellow-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-yellow-700 transition-colors">
                                Top Up Saldo
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <p class="text-xs text-gray-500 mt-4">
                * Pembayaran hanya dapat dilakukan menggunakan saldo Wallet
            </p>

            {{-- Pay Button --}}
            <form action="{{ route('checkout.pay') }}" method="POST" class="mt-6">
                @csrf
                <button type="submit" 
                        {{ !$canPay ? 'disabled' : '' }}
                        class="w-full py-3 rounded-lg font-semibold transition-colors {{ $canPay ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}">
                    {{ $canPay ? 'Bayar Sekarang' : 'Saldo Tidak Mencukupi' }}
                </button>
            </form>

            <a href="{{ route('cart.index') }}" class="block text-center text-gray-600 hover:text-gray-900 mt-4 text-sm">
                Kembali ke Keranjang
            </a>
        </div>
    </div>
</div>
@endsection
