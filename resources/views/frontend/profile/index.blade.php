@extends('frontend.layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8">Profil Saya</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Profile Header --}}
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-8">
            <div class="flex items-center">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center text-primary-600 text-2xl font-bold">
                    {{ strtoupper(substr($profile['full_name'] ?? 'U', 0, 2)) }}
                </div>
                <div class="ml-6 text-white">
                    <h2 class="text-2xl font-bold">{{ $profile['full_name'] ?? 'Nama Pengguna' }}</h2>
                    <p class="text-primary-200">{{ $profile['email'] }}</p>
                    @if(($profile['role'] ?? 'user') === 'admin')
                        <span class="inline-block mt-2 bg-white/20 text-white text-xs font-medium px-3 py-1 rounded-full">
                            Administrator
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Profile Details --}}
        <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nama Lengkap</label>
                    <p class="mt-1 text-gray-900">{{ $profile['full_name'] ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Email</label>
                    <p class="mt-1 text-gray-900">{{ $profile['email'] ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nomor Telepon</label>
                    <p class="mt-1 text-gray-900">{{ $profile['phone_number'] ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Tanggal Lahir</label>
                    <p class="mt-1 text-gray-900">
                        @if(!empty($profile['date_of_birth']))
                            {{ \Carbon\Carbon::parse($profile['date_of_birth'])->format('d F Y') }}
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Bergabung Sejak</label>
                    <p class="mt-1 text-gray-900">
                        @if(!empty($profile['created_at']))
                            {{ \Carbon\Carbon::parse($profile['created_at'])->format('d F Y') }}
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <a href="{{ route('profile.edit') }}" class="bg-primary-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors">
                    Edit Profil
                </a>
                <a href="{{ route('wishlist') }}" class="border border-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                    Lihat Wishlist
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
