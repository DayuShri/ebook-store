@extends('frontend.layouts.app')

@section('title', 'Detail Pengguna')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Detail Pengguna</h1>
            <p class="text-gray-600 mt-1">Informasi lengkap pengguna</p>
        </div>
        <a href="{{ route('admin.users.edit', $user['id']) }}" 
           class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid md:grid-cols-3 gap-6">
        {{-- Profile Card --}}
        <div class="md:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
                <div class="w-24 h-24 bg-primary-100 rounded-full flex items-center justify-center mx-auto">
                    <span class="text-primary-600 font-bold text-3xl">
                        {{ strtoupper(substr($user['profile']['full_name'] ?? $user['email'], 0, 2)) }}
                    </span>
                </div>
                <h2 class="mt-4 text-xl font-semibold text-gray-900">
                    {{ $user['profile']['full_name'] ?? 'Belum diisi' }}
                </h2>
                <p class="text-gray-500">{{ $user['email'] }}</p>
                
                <div class="flex items-center justify-center space-x-2 mt-4">
                    @if($user['role'] === 'admin')
                        <span class="px-3 py-1 text-sm font-medium bg-purple-100 text-purple-700 rounded-full">Admin</span>
                    @else
                        <span class="px-3 py-1 text-sm font-medium bg-gray-100 text-gray-700 rounded-full">User</span>
                    @endif
                    @if($user['is_active'] ?? true)
                        <span class="px-3 py-1 text-sm font-medium bg-green-100 text-green-700 rounded-full">Aktif</span>
                    @else
                        <span class="px-3 py-1 text-sm font-medium bg-red-100 text-red-700 rounded-full">Nonaktif</span>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mt-6">
                <h3 class="font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-3">
                    @if($user['is_active'] ?? true)
                        <form action="{{ route('admin.users.deactivate', $user['id']) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="w-full px-4 py-2 text-left text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                                    onclick="return confirm('Yakin ingin menonaktifkan pengguna ini?')">
                                Nonaktifkan Pengguna
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.users.activate', $user['id']) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="w-full px-4 py-2 text-left text-green-600 hover:bg-green-50 rounded-lg transition-colors">
                                Aktifkan Pengguna
                            </button>
                        </form>
                    @endif

                    @if($user['role'] === 'admin')
                        <form action="{{ route('admin.users.demote', $user['id']) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="w-full px-4 py-2 text-left text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors"
                                    onclick="return confirm('Yakin ingin menurunkan admin ini menjadi user biasa?')">
                                Demosi ke User
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.users.promote', $user['id']) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="w-full px-4 py-2 text-left text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                    onclick="return confirm('Yakin ingin mempromosikan pengguna ini menjadi admin?')">
                                Promosi ke Admin
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('admin.users.delete', $user['id']) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full px-4 py-2 text-left text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                onclick="return confirm('Yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.')">
                            Hapus Pengguna
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- User Details --}}
        <div class="md:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Informasi Pengguna</h3>
                
                <dl class="space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-gray-500">ID</dt>
                        <dd class="col-span-2 text-gray-900 font-mono text-sm">{{ $user['id'] }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="col-span-2 text-gray-900">{{ $user['email'] }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-gray-500">Nama Lengkap</dt>
                        <dd class="col-span-2 text-gray-900">{{ $user['profile']['full_name'] ?? '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-gray-500">Nomor Telepon</dt>
                        <dd class="col-span-2 text-gray-900">{{ $user['profile']['phone_number'] ?? '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-gray-500">Tanggal Lahir</dt>
                        <dd class="col-span-2 text-gray-900">
                            @if(!empty($user['profile']['date_of_birth']))
                                {{ \Carbon\Carbon::parse($user['profile']['date_of_birth'])->format('d M Y') }}
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-gray-500">Role</dt>
                        <dd class="col-span-2">
                            @if($user['role'] === 'admin')
                                <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-700 rounded-full">Admin</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">User</span>
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-gray-500">Status</dt>
                        <dd class="col-span-2">
                            @if($user['is_active'] ?? true)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">Aktif</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">Nonaktif</span>
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-gray-500">Terdaftar</dt>
                        <dd class="col-span-2 text-gray-900">
                            @if(!empty($user['created_at']))
                                {{ \Carbon\Carbon::parse($user['created_at'])->format('d M Y, H:i') }}
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-gray-500">Login Terakhir</dt>
                        <dd class="col-span-2 text-gray-900">
                            @if(!empty($user['last_login_at']))
                                {{ \Carbon\Carbon::parse($user['last_login_at'])->format('d M Y, H:i') }}
                            @else
                                Belum pernah login
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- Back Link --}}
    <div class="mt-6">
        <a href="{{ route('admin.users') }}" class="text-primary-600 hover:text-primary-700 font-medium">
            &larr; Kembali ke Daftar Pengguna
        </a>
    </div>
</div>
@endsection
