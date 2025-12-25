@extends('frontend.layouts.app')

@section('title', 'Detail Kategori')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-10">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
            Detail Kategori
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Informasi lengkap kategori buku.
        </p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-6 md:p-8 space-y-6">

        {{-- Nama --}}
        <div>
            <div class="text-sm font-semibold text-slate-600">Nama</div>
            <div class="text-base text-slate-900 mt-1">
                {{ $category->name }}
            </div>
        </div>

        {{-- Slug --}}
        <div>
            <div class="text-sm font-semibold text-slate-600">Slug</div>
            <div class="text-base text-slate-900 mt-1">
                {{ $category->slug }}
            </div>
        </div>

        {{-- Parent --}}
        <div>
            <div class="text-sm font-semibold text-slate-600">Kategori Induk</div>
            <div class="text-base text-slate-900 mt-1">
                {{ $category->parent?->name ?? 'Kategori Utama' }}
            </div>
        </div>

        {{-- Subkategori --}}
        <div>
            <div class="text-sm font-semibold text-slate-600 mb-2">
                Subkategori
            </div>

            @if($category->children->count())
                <ul class="list-disc pl-5 text-slate-800 space-y-1">
                    @foreach($category->children as $child)
                        <li>{{ $child->name }}</li>
                    @endforeach
                </ul>
            @else
                <div class="text-sm text-slate-500">
                    Tidak memiliki subkategori.
                </div>
            @endif
        </div>

        {{-- Action --}}
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('catalog.categories.index') }}"
               class="rounded-xl border border-slate-300
                      px-5 py-2.5 text-sm font-semibold
                      text-slate-700 hover:bg-slate-50">
                Kembali
            </a>

            <a href="{{ route('catalog.categories.edit', $category) }}"
               class="rounded-xl bg-indigo-600
                      px-5 py-2.5 text-sm font-semibold
                      text-white hover:bg-indigo-700">
                Edit
            </a>
        </div>

    </div>
</div>
@endsection
