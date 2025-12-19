@extends('frontend.layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Tambah Pengguna Baru</h1>
        <p class="text-gray-600 mt-1">Buat akun pengguna baru</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            {{-- Email --}}
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('email') border-red-500 @enderror"
                       placeholder="email@example.com" required>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                <input type="password" name="password" id="password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('password') border-red-500 @enderror"
                       placeholder="Minimal 6 karakter" required>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Full Name --}}
            <div class="mb-6">
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('full_name') border-red-500 @enderror"
                       placeholder="Nama lengkap pengguna" required>
                @error('full_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Role --}}
            <div class="mb-6">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                <select name="role" id="role"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('role') border-red-500 @enderror"
                        required>
                    <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="{{ route('admin.users') }}" class="text-gray-600 hover:text-gray-900">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
