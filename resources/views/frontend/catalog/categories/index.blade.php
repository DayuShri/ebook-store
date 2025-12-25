@extends('frontend.layouts.app')

@section('title', 'Manajemen Kategori')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-10">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
                Manajemen Kategori
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola kategori buku dan subkategori.
            </p>
        </div>

        <a href="{{ route('catalog.categories.create') }}"
           class="inline-flex items-center rounded-xl
                  bg-indigo-600 px-5 py-2.5
                  text-sm font-semibold text-white
                  hover:bg-indigo-700">
            + Tambah Kategori
        </a>
    </div>



    {{-- ================= TABLE ================= --}}
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold">Nama</th>
                    <th class="text-left px-5 py-3 font-semibold">Induk Kategori</th>
                    <th class="text-left px-5 py-3 font-semibold">Slug</th>
                    <th class="text-right px-5 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">

            @forelse($categories as $cat)
                {{-- ================= PARENT ================= --}}
                <tr class="bg-slate-50 hover:bg-slate-100">
                    <td class="px-5 py-3 font-semibold text-slate-900 flex items-center gap-2">
                        <span class="text-indigo-600">🏷️</span>
                        {{ $cat['name'] }}

                        <span class="ml-2 text-xs px-2 py-0.5 rounded-full
                                     bg-indigo-100 text-indigo-700">
                            Induk
                        </span>
                    </td>

                    <td class="px-5 py-3 text-slate-400">
                        —
                    </td>

                    <td class="px-5 py-3 text-slate-600">
                        {{ $cat['slug'] ?? '-' }}
                    </td>

                    <td class="px-5 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('catalog.categories.show', $cat['id']) }}"
                               class="rounded-lg border px-3 py-1.5 text-slate-700 hover:bg-slate-50">
                                Detail
                            </a>

                            <a href="{{ route('catalog.categories.edit', $cat['id']) }}"
                               class="rounded-lg bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100">
                                Edit
                            </a>

                            <button type="button"
                                    onclick="openDeleteModal('{{ $cat['id'] }}','{{ $cat['name'] }}')"
                                    class="rounded-lg bg-red-50 px-3 py-1.5 text-red-700 hover:bg-red-100">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>

                {{-- ================= CHILD ================= --}}
                @foreach($cat['children'] ?? [] as $child)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 pl-10 flex items-center gap-2 text-slate-700">
                            <span class="text-slate-400">↳</span>
                            {{ $child['name'] }}

                            <span class="ml-2 text-xs px-2 py-0.5 rounded-full
                                         bg-slate-100 text-slate-600">
                                Sub
                            </span>
                        </td>

                        <td class="px-5 py-3">
                            <span class="inline-block text-xs px-2 py-1 rounded-full
                                         bg-slate-200 text-slate-700">
                                {{ $cat['name'] }}
                            </span>
                        </td>

                        <td class="px-5 py-3 text-slate-600">
                            {{ $child['slug'] ?? '-' }}
                        </td>

                        <td class="px-5 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('catalog.categories.show', $child['id']) }}"
                                   class="rounded-lg border px-3 py-1.5 text-slate-700 hover:bg-slate-50">
                                    Detail
                                </a>

                                <a href="{{ route('catalog.categories.edit', $child['id']) }}"
                                   class="rounded-lg bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100">
                                    Edit
                                </a>

                                <button type="button"
                                        onclick="openDeleteModal('{{ $child['id'] }}','{{ $child['name'] }}')"
                                        class="rounded-lg bg-red-50 px-3 py-1.5 text-red-700 hover:bg-red-100">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach

            @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center text-slate-500">
                        Belum ada kategori.
                    </td>
                </tr>
            @endforelse

            </tbody>
        </table>
    </div>
</div>

{{-- ================= DELETE MODAL ================= --}}
<div id="deleteModal"
     class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm
            flex items-center justify-center">

    <div class="bg-white w-full max-w-md mx-4
                rounded-2xl shadow-xl p-6 space-y-5">

        <div>
            <h2 class="text-xl font-bold text-slate-900">
                Hapus Kategori
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Tindakan ini tidak dapat dibatalkan.
            </p>
        </div>

        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
            <p class="text-sm text-red-700">
                Anda akan menghapus kategori:
            </p>
            <p id="deleteCategoryName"
               class="mt-1 font-semibold text-red-800">—</p>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <button type="button"
                    onclick="closeDeleteModal()"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold
                           text-slate-700 hover:bg-slate-50">
                Batal
            </button>

            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="rounded-xl bg-red-600 px-4 py-2
                               text-sm font-semibold text-white hover:bg-red-700">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openDeleteModal(id, name) {
    document.getElementById('deleteCategoryName').innerText = name;
    document.getElementById('deleteForm').action = `/catalog/categories/${id}`;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
@endsection
