<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Membaca') - E-Book Store</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Distraction-free reading mode */
        body.reading-mode {
            overflow: hidden;
        }
        
        .reader-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .reader-toolbar {
            flex-shrink: 0;
        }
        
        .reader-content {
            flex: 1;
            overflow: hidden;
        }
    </style>
    
    @stack('styles')
</head>
<body class="reading-mode bg-gray-900">
    <div class="reader-container">
        <!-- Reader Toolbar -->
        <header class="reader-toolbar bg-gray-800 text-white px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <!-- Back Button -->
                <a href="{{ route('library.index') }}" class="flex items-center text-gray-300 hover:text-white transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span class="hidden sm:inline">Kembali</span>
                </a>
                
                <!-- Book Title -->
                <div class="hidden md:block">
                    <h1 class="text-lg font-medium truncate max-w-md">@yield('book_title', 'Judul Buku')</h1>
                </div>
            </div>
            
            <!-- Reading Progress -->
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-400">
                    Halaman <span id="current-page">@yield('current_page', '1')</span> dari <span id="total-pages">@yield('total_pages', '1')</span>
                </span>
            </div>
            
            <!-- Controls -->
            <div class="flex items-center space-x-2">
                <!-- Zoom Controls -->
                <button id="zoom-out" class="p-2 text-gray-400 hover:text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path>
                    </svg>
                </button>
                <span id="zoom-level" class="text-sm text-gray-400 min-w-[3rem] text-center">100%</span>
                <button id="zoom-in" class="p-2 text-gray-400 hover:text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                    </svg>
                </button>
                
                <!-- Fullscreen -->
                <button id="fullscreen-toggle" class="p-2 text-gray-400 hover:text-white rounded-lg hover:bg-gray-700 transition-colors ml-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    </svg>
                </button>
            </div>
        </header>
        
        <!-- Reader Content -->
        <main class="reader-content relative bg-gray-100">
            @yield('reader_content')
            
            <!-- Page Navigation -->
            <button id="prev-page" class="absolute left-4 top-1/2 -translate-y-1/2 p-3 bg-white/90 rounded-full shadow-lg hover:bg-white transition-colors">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button id="next-page" class="absolute right-4 top-1/2 -translate-y-1/2 p-3 bg-white/90 rounded-full shadow-lg hover:bg-white transition-colors">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </main>
        
        <!-- Progress Bar -->
        <div class="bg-gray-800 h-1">
            <div id="reading-progress-bar" class="h-full bg-primary-500 transition-all duration-300" style="width: @yield('progress_percentage', '0')%"></div>
        </div>
    </div>
    
    @stack('scripts')
    
    <script>
        // Basic reader controls
        document.addEventListener('DOMContentLoaded', function() {
            const fullscreenBtn = document.getElementById('fullscreen-toggle');
            
            fullscreenBtn?.addEventListener('click', function() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                } else {
                    document.exitFullscreen();
                }
            });
        });
    </script>
</body>
</html>
