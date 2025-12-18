@extends('frontend.layouts.app')

@section('title', 'Top Up Saldo')

@section('content')
<div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('wallet.index') }}" class="text-gray-600 hover:text-gray-900 flex items-center text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mt-4">Top Up Saldo</h1>
        <p class="text-gray-600 mt-1">Saldo saat ini: <span class="font-semibold text-primary-600">Rp {{ number_format($balance, 0, ',', '.') }}</span></p>
    </div>

    <form action="{{ route('wallet.topup.submit') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        @csrf

        {{-- Amount Selection --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Pilih Nominal</label>
            <div class="grid grid-cols-3 gap-3">
                @foreach([50000, 100000, 200000, 300000, 500000, 1000000] as $preset)
                    <label class="relative">
                        <input type="radio" name="amount" value="{{ $preset }}" class="peer sr-only" {{ $preset === 100000 ? 'checked' : '' }}>
                        <div class="p-3 text-center border-2 rounded-lg cursor-pointer transition-colors peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300">
                            <span class="font-semibold text-gray-900">Rp {{ number_format($preset / 1000, 0) }}K</span>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Custom Amount --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Atau masukkan nominal lain</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                <input type="number" name="custom_amount" placeholder="Minimal 10.000" min="10000" max="10000000"
                       class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
            </div>
            <p class="text-xs text-gray-500 mt-1">Minimal Rp 10.000, maksimal Rp 10.000.000</p>
        </div>

        {{-- Payment Method --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Metode Pembayaran</label>
            <div class="space-y-3">
                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:border-gray-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50">
                    <input type="radio" name="method" value="bank_transfer" class="w-4 h-4 text-primary-600" checked>
                    <div class="ml-3 flex items-center">
                        <svg class="w-8 h-8 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Transfer Bank</p>
                            <p class="text-sm text-gray-500">BCA, Mandiri, BNI, BRI</p>
                        </div>
                    </div>
                </label>

                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:border-gray-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50">
                    <input type="radio" name="method" value="qris" class="w-4 h-4 text-primary-600">
                    <div class="ml-3 flex items-center">
                        <svg class="w-8 h-8 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">QRIS</p>
                            <p class="text-sm text-gray-500">Scan dengan e-wallet apapun</p>
                        </div>
                    </div>
                </label>

                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:border-gray-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50">
                    <input type="radio" name="method" value="credit_card" class="w-4 h-4 text-primary-600">
                    <div class="ml-3 flex items-center">
                        <svg class="w-8 h-8 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Kartu Kredit / Debit</p>
                            <p class="text-sm text-gray-500">Visa, Mastercard</p>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="w-full bg-primary-600 text-white py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
            Top Up Sekarang
        </button>

        <p class="text-xs text-gray-500 text-center mt-4">
            Dengan melanjutkan, Anda menyetujui syarat dan ketentuan yang berlaku
        </p>
    </form>
</div>

<script>
    // Clear custom amount when selecting preset
    document.querySelectorAll('input[name="amount"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelector('input[name="custom_amount"]').value = '';
        });
    });

    // Use custom amount when filled
    document.querySelector('input[name="custom_amount"]').addEventListener('input', function() {
        if (this.value) {
            document.querySelectorAll('input[name="amount"]').forEach(r => r.checked = false);
        }
    });
</script>
@endsection
