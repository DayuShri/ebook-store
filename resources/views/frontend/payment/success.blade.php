@extends('frontend.layouts.app')

@section('title', 'Pembayaran Berhasil')

@section('content')
<div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
        {{-- Success Icon --}}
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Pembayaran Berhasil!</h1>
        <p class="text-gray-600 mb-6">Top-up saldo Anda sedang diproses. Saldo akan bertambah setelah pembayaran dikonfirmasi.</p>

        {{-- Info Box --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8 text-left">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-sm text-blue-800 font-medium">Informasi</p>
                    <p class="text-sm text-blue-700 mt-1">
                        Proses konfirmasi pembayaran biasanya memerlukan waktu beberapa menit. 
                        Silakan cek halaman dompet Anda untuk melihat saldo terbaru.
                    </p>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('wallet.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                Lihat Dompet Saya
            </a>
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
