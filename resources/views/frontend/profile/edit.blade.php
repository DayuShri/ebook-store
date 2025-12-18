@extends('frontend.layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('profile.index') }}" class="text-gray-600 hover:text-gray-900 flex items-center text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mt-4">Edit Profil</h1>
    </div>

    <form action="{{ route('profile.update') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        @csrf
        @method('PUT')

        {{-- Profile Picture --}}
        <div class="mb-6 text-center">
            <div class="w-24 h-24 mx-auto bg-primary-100 rounded-full flex items-center justify-center text-primary-600 text-2xl font-bold">
                {{ strtoupper(substr($profile['full_name'] ?? 'U', 0, 2)) }}
            </div>
            <p class="text-sm text-gray-500 mt-2">Foto profil</p>
        </div>

        {{-- Full Name --}}
        <div class="mb-4">
            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
            <input type="text" name="full_name" id="full_name" 
                   value="{{ old('full_name', $profile['full_name'] ?? '') }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary-500 focus:border-primary-500 @error('full_name') border-red-500 @enderror"
                   required>
            @error('full_name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email (readonly) --}}
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" id="email" 
                   value="{{ $profile['email'] ?? '' }}"
                   class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2 text-gray-500" 
                   disabled>
            <p class="text-xs text-gray-500 mt-1">Email tidak dapat diubah</p>
        </div>

        {{-- Phone Number --}}
        <div class="mb-4">
            <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
            <input type="tel" name="phone_number" id="phone_number" 
                   value="{{ old('phone_number', $profile['phone_number'] ?? '') }}"
                   placeholder="+62812345678"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary-500 focus:border-primary-500">
        </div>

        {{-- Date of Birth --}}
        <div class="mb-6">
            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
            <input type="date" name="date_of_birth" id="date_of_birth" 
                   value="{{ old('date_of_birth', $profile['date_of_birth'] ?? '') }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary-500 focus:border-primary-500">
        </div>

        {{-- Submit --}}
        <div class="flex gap-4">
            <button type="submit" class="flex-1 bg-primary-600 text-white py-2 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                Simpan Perubahan
            </button>
            <a href="{{ route('profile.index') }}" class="flex-1 text-center border border-gray-200 text-gray-700 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
