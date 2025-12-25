@extends('frontend.layouts.app')

@section('title', 'Edit Voucher')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                <a href="{{ route('admin.vouchers') }}" class="hover:text-primary-600">Vouchers</a>
                <span>/</span>
                <span class="text-gray-900 font-medium">Edit Voucher</span>
            </div>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Edit Voucher: {{ $voucher->code }}</h1>
                <span
                    class="px-3 py-1 text-xs font-semibold rounded-full {{ $voucher->is_active && $voucher->valid_until >= now() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $voucher->is_active && $voucher->valid_until >= now() ? 'Aktif' : 'Nonaktif/Expired' }}
                </span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <form action="{{ route('admin.vouchers.update', $voucher->id) }}" method="POST" class="p-6 md:p-8 space-y-6">
                @csrf
                @method('PUT')

                @if(session('error'))
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <ul class="list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode Voucher <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="code" id="code" value="{{ old('code', $voucher->code) }}"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 focus:bg-white focus:border-primary-500 focus:ring-primary-500 uppercase placeholder-gray-400"
                            required>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <input type="text" name="description" id="description"
                            value="{{ old('description', $voucher->description) }}"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 focus:bg-white focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="discount_type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Diskon <span
                                class="text-red-500">*</span></label>
                        <select name="discount_type" id="discount_type"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 focus:bg-white focus:border-primary-500 focus:ring-primary-500">
                            <option value="fixed" {{ old('discount_type', $voucher->discount_type) == 'fixed' ? 'selected' : '' }}>Nominal (Rp)</option>
                            <option value="percentage" {{ old('discount_type', $voucher->discount_type) == 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                        </select>
                    </div>
                    <div>
                        <label for="discount_value" class="block text-sm font-medium text-gray-700 mb-1">Nilai Diskon <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="discount_value" id="discount_value"
                            value="{{ old('discount_value', $voucher->discount_value) }}"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 focus:bg-white focus:border-primary-500 focus:ring-primary-500"
                            min="0" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="min_purchase_amount" class="block text-sm font-medium text-gray-700 mb-1">Min. Belanja
                            (Rp)</label>
                        <input type="number" name="min_purchase_amount" id="min_purchase_amount"
                            value="{{ old('min_purchase_amount', $voucher->min_purchase_amount) }}"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 focus:bg-white focus:border-primary-500 focus:ring-primary-500"
                            min="0">
                    </div>
                    <div>
                        <label for="max_discount_amount" class="block text-sm font-medium text-gray-700 mb-1">Max. Potongan
                            (Rp)</label>
                        <input type="number" name="max_discount_amount" id="max_discount_amount"
                            value="{{ old('max_discount_amount', $voucher->max_discount_amount) }}"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 focus:bg-white focus:border-primary-500 focus:ring-primary-500"
                            min="0">
                    </div>
                    <div>
                        <label for="quota" class="block text-sm font-medium text-gray-700 mb-1">Kuota Penggunaan</label>
                        <input type="number" name="quota" id="quota" value="{{ old('quota', $voucher->quota) }}"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 focus:bg-white focus:border-primary-500 focus:ring-primary-500"
                            min="1">
                        <p class="text-xs text-gray-500 mt-1">Terpakai: {{ $voucher->used_count }} kali</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="valid_from" class="block text-sm font-medium text-gray-700 mb-1">Berlaku Mulai <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="valid_from" id="valid_from"
                            value="{{ old('valid_from', $voucher->valid_from->format('Y-m-d')) }}"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 focus:bg-white focus:border-primary-500 focus:ring-primary-500"
                            required>
                    </div>
                    <div>
                        <label for="valid_until" class="block text-sm font-medium text-gray-700 mb-1">Berlaku Sampai <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="valid_until" id="valid_until"
                            value="{{ old('valid_until', $voucher->valid_until->format('Y-m-d')) }}"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 focus:bg-white focus:border-primary-500 focus:ring-primary-500"
                            required>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $voucher->is_active) ? 'checked' : '' }}
                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Aktifkan Voucher
                    </label>
                </div>

                <div class="pt-6 border-t border-gray-200 flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.vouchers') }}"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium shadow-sm transition-all focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection