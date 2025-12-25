@extends('frontend.layouts.app')

@section('title', 'Manajemen Buku')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-10">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
                Manajemen Buku
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola data buku yang tersedia di katalog.
            </p>
        </div>

        <a href="{{ route('catalog.books.create') }}"
           class="inline-flex items-center rounded-xl
                  bg-indigo-600 px-5 py-2.5
                  text-sm font-semibold text-white
                  hover:bg-indigo-700">
            + Tambah Buku
        </a>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold">Judul</th>
                    <th class="text-left px-5 py-3 font-semibold">Kategori</th>
                    <th class="text-right px-5 py-3 font-semibold">Harga</th>
                    <th class="text-center px-5 py-3 font-semibold">Status</th>
                    <th class="text-right px-5 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
            @forelse($books as $book)
                <tr class="hover:bg-slate-50">

                    {{-- JUDUL --}}
                    <td class="px-5 py-3 font-medium text-slate-900">
                        {{ $book->title }}
                    </td>

                    {{-- KATEGORI --}}
                    <td class="px-5 py-3">
                        <div class="flex flex-wrap gap-1">
                            @forelse($book->categories as $cat)
                                <span class="text-xs px-2 py-1 rounded-full
                                             bg-indigo-100 text-indigo-700">
                                    {{ $cat->name }}
                                </span>
                            @empty
                                <span class="text-xs text-slate-400">—</span>
                            @endforelse
                        </div>
                    </td>

                    {{-- HARGA --}}
                    <td class="px-5 py-3 text-right font-semibold">
                        Rp {{ number_format($book->price, 0, ',', '.') }}
                    </td>

                    {{-- STATUS --}}
                    <td class="px-5 py-3 text-center">
                        @if($book->is_active)
                            <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">
                                Aktif
                            </span>
                        @else
                            <span class="text-xs px-2 py-1 rounded-full bg-slate-200 text-slate-600">
                                Nonaktif
                            </span>
                        @endif
                    </td>

                    {{-- AKSI --}}
                    <td class="px-5 py-3">
                        <div class="flex justify-end gap-2">

                            {{-- DETAIL --}}
                           <a href="{{ route('catalog.books.show', $book->id) }}"
   class="rounded-lg border px-3 py-1.5 hover:bg-slate-50">
    Detail
</a>

                            {{-- EDIT --}}
                          <a href="{{ route('catalog.books.edit', $book->id) }}"
   class="rounded-lg bg-indigo-50 px-3 py-1.5
          text-indigo-700 hover:bg-indigo-100">
    Edit
</a>


                            {{-- DELETE --}}
                            <button type="button"
                                    onclick="openDeleteModal('{{ route('catalog.books.destroy', $book->id) }}','{{ $book->title }}')"
                                    class="rounded-lg bg-red-50 px-3 py-1.5
                                           text-red-700 hover:bg-red-100">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-slate-500">
                        Belum ada buku.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ================= DELETE MODAL ================= --}}
<div id="deleteModal"
     class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white max-w-md w-full rounded-2xl p-6">
        <h2 class="text-lg font-bold mb-3">Hapus Buku</h2>

        <p class="text-sm text-red-700 mb-4">
            Anda akan menghapus buku:
            <strong id="deleteBookTitle"></strong>
        </p>

        <div class="flex justify-end gap-3">
            <button onclick="closeDeleteModal()"
                    class="border px-4 py-2 rounded-lg">
                Batal
            </button>

            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button class="bg-red-600 text-white px-4 py-2 rounded-lg">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openDeleteModal(action, title) {
    document.getElementById('deleteBookTitle').innerText = title;
    document.getElementById('deleteForm').action = action;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
@endsection
