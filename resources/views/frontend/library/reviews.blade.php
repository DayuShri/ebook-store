@extends('frontend.layouts.app')

@section('title', 'Review Buku')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8">
            <a href="{{ route('library.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Perpustakaan
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Review Buku</h1>
            <p class="mt-2 text-gray-600">Lihat dan berikan review untuk buku yang telah Anda baca</p>
        </div>

        {{-- Reviews List --}}
        <div class="space-y-6">
            {{-- Empty State --}}
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada review</h3>
                <p class="mt-1 text-sm text-gray-500">Mulai berikan review untuk buku yang telah Anda baca</p>
                <div class="mt-6">
                    <a href="{{ route('library.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Lihat Perpustakaan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
