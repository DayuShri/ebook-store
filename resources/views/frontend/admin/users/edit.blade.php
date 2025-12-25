@extends('frontend.layouts.app')

@section('title', 'Edit Pengguna')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Edit Pengguna</h1>
        <p class="text-gray-600 mt-1">Perbarui informasi pengguna</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- User Info Card --}}
    <div class="bg-gray-50 rounded-xl p-4 mb-6">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                <span class="text-primary-600 font-semibold">
                    {{ strtoupper(substr($user['profile']['full_name'] ?? $user['email'], 0, 2)) }}
                </span>
            </div>
            <div class="ml-4">
                <p class="font-medium text-gray-900">{{ $user['email'] }}</p>
                <div class="flex items-center space-x-2 mt-1">
                    @if($user['role'] === 'admin')
                        <span class="px-2 py-0.5 text-xs font-medium bg-purple-100 text-purple-700 rounded-full">Admin</span>
                    @else
                        <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">User</span>
                    @endif
                    @if($user['is_active'] ?? true)
                        <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">Aktif</span>
                    @else
                        <span class="px-2 py-0.5 text-xs font-medium bg-red-100 text-red-700 rounded-full">Nonaktif</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.users.update', $user['id']) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Full Name --}}
            <div class="mb-6">
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                <input type="text" name="full_name" id="full_name" 
                       value="{{ old('full_name', $user['profile']['full_name'] ?? '') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('full_name') border-red-500 @enderror"
                       placeholder="Nama lengkap pengguna" required>
                @error('full_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone Number --}}
            <div class="mb-6">
                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                <input type="text" name="phone_number" id="phone_number" 
                       value="{{ old('phone_number', $user['profile']['phone_number'] ?? '') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('phone_number') border-red-500 @enderror"
                       placeholder="+62812345678">
                @error('phone_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Note about email/role --}}
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-700 text-sm">
                <strong>Catatan:</strong> Email tidak dapat diubah. Untuk mengubah role, gunakan tombol Promosi/Demosi di halaman daftar pengguna.
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="{{ route('admin.users') }}" class="text-gray-600 hover:text-gray-900">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
