@extends('frontend.layouts.app')

@section('title', 'Upload File Buku')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-10">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">
            Upload File Buku
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Buku: <span class="font-semibold">{{ $book->title }}</span>
        </p>
    </div>

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="mb-5 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <form
            method="POST"
            action="{{ route('catalog.books.upload.store', $book->id) }}"
            enctype="multipart/form-data"
        >
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    File Buku (PDF)
                </label>

                <input
                    type="file"
                    name="book_file"
                    accept="application/pdf"
                    required
                    class="block w-full text-sm text-slate-700
                           file:mr-4 file:rounded-lg
                           file:border-0
                           file:bg-indigo-50
                           file:px-4 file:py-2
                           file:text-sm file:font-semibold
                           file:text-indigo-700
                           hover:file:bg-indigo-100"
                >

                <p class="text-xs text-slate-500 mt-1">
                    Maksimal 20MB. Format PDF.
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <a
                    href="{{ route('catalog.books.index') }}"
                    class="rounded-lg border px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Lewati
                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                >
                    Upload File
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
