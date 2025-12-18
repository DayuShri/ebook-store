@extends('frontend.layouts.app')

@section('title', 'Dompet Saya')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8">Dompet Saya</h1>

    {{-- Balance Card --}}
    <div class="bg-gradient-to-br from-primary-600 to-primary-800 rounded-2xl p-8 text-white mb-8">
        <p class="text-primary-200 text-sm">Saldo Tersedia</p>
        <p class="text-4xl font-bold mt-2">Rp {{ number_format($balance, 0, ',', '.') }}</p>
        <div class="mt-6">
            <a href="{{ route('wallet.topup') }}" class="inline-block bg-white text-primary-700 px-6 py-3 rounded-lg font-semibold hover:bg-primary-50 transition-colors">
                + Top Up Saldo
            </a>
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat Transaksi</h2>
        </div>

        @if(count($transactions) > 0)
            <div class="divide-y divide-gray-100">
                @foreach($transactions as $transaction)
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4
                                    {{ $transaction['type'] === 'top_up' ? 'bg-green-100' : '' }}
                                    {{ $transaction['type'] === 'payment' ? 'bg-red-100' : '' }}
                                    {{ $transaction['type'] === 'refund' ? 'bg-blue-100' : '' }}">
                                    @if($transaction['type'] === 'top_up')
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    @elseif($transaction['type'] === 'payment')
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $transaction['description'] }}</p>
                                    <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($transaction['created_at'])->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold {{ $transaction['amount'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $transaction['amount'] >= 0 ? '+' : '' }}Rp {{ number_format(abs($transaction['amount']), 0, ',', '.') }}
                                </p>
                                <p class="text-sm text-gray-400">{{ $transaction['reference_id'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-12 text-center">
                <p class="text-gray-500">Belum ada transaksi</p>
            </div>
        @endif
    </div>
</div>
@endsection
