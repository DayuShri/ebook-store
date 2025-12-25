@extends('frontend.layouts.app')

@section('title', 'Edit Kategori')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-10">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
            Edit Kategori
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Perbarui informasi kategori.
        </p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200">
        <form method="POST"
              action="{{ route('catalog.categories.update', $category) }}"
              class="p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')

            {{-- Error --}}
            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <div class="font-semibold mb-1">Ada yang perlu diperbaiki:</div>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Nama --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Nama Kategori
                </label>
                <input name="name"
                       value="{{ old('name', $category->name) }}"
                       required
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                              focus:outline-none focus:ring-2
                              focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Contoh: Fiksi, Bisnis, Self Development">
                @error('name')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Slug --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Slug <span class="text-slate-400 text-xs">(Opsional)</span>
                </label>
                <input name="slug"
                       value="{{ old('slug', $category->slug) }}"
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                              focus:outline-none focus:ring-2
                              focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Otomatis jika dikosongkan">
                @error('slug')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Parent Category --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Kategori Induk <span class="text-slate-400 text-xs">(Opsional)</span>
                </label>

                <select name="parent_id"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm
                               focus:outline-none focus:ring-2
                               focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">
                        — Tanpa Kategori Induk —
                    </option>

                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}"
                            @selected(old('parent_id', $category->parent_id) == $parent->id)>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>


                <p class="mt-1 text-xs text-slate-500">
                    Digunakan jika kategori ini merupakan sub-kategori dari kategori lain.
                </p>

                @error('parent_id')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Action --}}
            <div class="flex flex-col sm:flex-row gap-3 sm:justify-end pt-4">
                <a href="{{ route('catalog.categories.index') }}"
                   class="inline-flex items-center justify-center rounded-xl
                          border border-slate-300 px-5 py-2.5
                          text-sm font-semibold text-slate-700
                          hover:bg-slate-50">
                    Batal
                </a>

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl
                               bg-indigo-600 px-5 py-2.5
                               text-sm font-semibold text-white
                               hover:bg-indigo-700
                               focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Update
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
