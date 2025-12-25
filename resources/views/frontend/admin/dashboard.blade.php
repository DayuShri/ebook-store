@extends('frontend.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="text-gray-600 mt-1">Selamat datang di panel admin</p>
        </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('catalog.categories.index') }}" class="p-4 border border-gray-200 rounded-lg text-center hover:border-primary-500 hover:bg-primary-50 transition-colors">
                <svg class="w-8 h-8 mx-auto text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span class="block mt-2 text-sm font-medium">Manajemen Kategori</span>
            </a>
             <a href="{{ route('catalog.books.index') }}" class="p-4 border border-gray-200 rounded-lg text-center hover:border-primary-500 hover:bg-primary-50 transition-colors">
                <svg class="w-8 h-8 mx-auto text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span class="block mt-2 text-sm font-medium">Manajemen Buku</span>
            </a>
            <a href="{{ route('admin.users') }}" class="p-4 border border-gray-200 rounded-lg text-center hover:border-primary-500 hover:bg-primary-50 transition-colors">
                <svg class="w-8 h-8 mx-auto text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                <span class="block mt-2 text-sm font-medium">Kelola Pengguna</span>
            </a>
            <a href="#" class="p-4 border border-gray-200 rounded-lg text-center hover:border-primary-500 hover:bg-primary-50 transition-colors">
                <svg class="w-8 h-8 mx-auto text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <span class="block mt-2 text-sm font-medium">Kelola Voucher</span>
            </a>
        </div>
    </div>
    </div>
@endsection