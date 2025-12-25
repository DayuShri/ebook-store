<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - E-Book Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center space-x-2">
                <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span class="text-2xl font-bold text-gray-900">BookStore</span>
            </a>
        </div>

        {{-- Login Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <h1 class="text-2xl font-bold text-gray-900 text-center">Selamat Datang Kembali</h1>
            <p class="text-gray-600 text-center mt-2">Masuk ke akun Anda</p>

            @if ($errors->any())
                <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    @foreach ($errors->all() as $error)
                        <p class="text-red-600 text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div id="error-message" class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4 hidden">
                <p class="text-red-600 text-sm" id="error-text"></p>
            </div>

            <form id="login-form" class="mt-8">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="nama@email.com" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="password"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="••••••••" required>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                    </label>
                </div>

                <button type="submit" id="login-button" class="w-full bg-primary-600 text-white py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                    Masuk
                </button>
            </form>

            <p class="mt-6 text-center text-gray-600">
                Belum punya akun? 
                <a href="{{ route('register') }}" class="text-primary-600 font-medium hover:text-primary-700">Daftar sekarang</a>
            </p>
        </div>

        {{-- Back to Home --}}
        <p class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700 text-sm flex items-center justify-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke beranda
            </a>
        </p>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const button = document.getElementById('login-button');
            const errorDiv = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');
            
            // Disable button
            button.disabled = true;
            button.textContent = 'Memproses...';
            errorDiv.classList.add('hidden');
            
            const formData = {
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
            };
            
            try {
                const response = await fetch('/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success && data.redirect) {
                    // Redirect to library
                    window.location.href = data.redirect;
                } else {
                    errorText.textContent = data.message || 'Email atau password tidak valid.';
                    errorDiv.classList.remove('hidden');
                    button.disabled = false;
                    button.textContent = 'Masuk';
                }
            } catch (error) {
                console.error('Login error:', error);
                errorText.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                errorDiv.classList.remove('hidden');
                button.disabled = false;
                button.textContent = 'Masuk';
            }
        });
    </script>
</body>
</html>
