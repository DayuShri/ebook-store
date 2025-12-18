@extends('frontend.layouts.reader')

@section('title', 'Membaca: ' . $book['title'])
@section('book_title', $book['title'])
@section('current_page', $progress['last_page_read'])
@section('total_pages', $progress['total_pages'])
@section('progress_percentage', $progress['progress_percentage'])

@section('reader_content')
<div class="h-full flex items-center justify-center p-4">
    {{-- Mock PDF Viewer --}}
    <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-full overflow-hidden" id="reader-wrapper">
        {{-- Mock Book Content --}}
        <div class="p-8 md:p-12 min-h-[60vh] max-h-[80vh] overflow-y-auto bg-[#fefefe]" id="book-content">
            <div class="prose max-w-none">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $book['title'] }}</h1>
                @if(!empty($book['subtitle']))
                    <p class="text-xl text-gray-600 mb-8">{{ $book['subtitle'] }}</p>
                @endif
                
                <hr class="my-8">

                <p class="text-gray-400 text-sm mb-4">Halaman {{ $progress['last_page_read'] }} dari {{ $progress['total_pages'] }}</p>

                {{-- Mock Book Text --}}
                <div class="text-gray-800 leading-relaxed space-y-4">
                    <p>{{ $book['synopsis'] ?? 'Konten buku akan ditampilkan di sini.' }}</p>
                    
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                    
                    <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                    
                    <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>
                    
                    <p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Reader controls
    const currentPageEl = document.getElementById('current-page');
    const totalPagesEl = document.getElementById('total-pages');
    const progressBar = document.getElementById('reading-progress-bar');
    const zoomLevel = document.getElementById('zoom-level');
    const bookContent = document.getElementById('book-content');
    
    let currentPage = {{ $progress['last_page_read'] }};
    const totalPages = {{ $progress['total_pages'] }};
    let zoom = 100;

    function updateProgress() {
        currentPageEl.textContent = currentPage;
        const percentage = (currentPage / totalPages) * 100;
        progressBar.style.width = percentage + '%';
        
        // Save progress to server
        fetch('{{ route("library.progress", $book["id"]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                current_page: currentPage,
                total_pages: totalPages
            })
        });
    }

    // Previous page
    document.getElementById('prev-page').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            updateProgress();
        }
    });

    // Next page
    document.getElementById('next-page').addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentPage++;
            updateProgress();
        }
    });

    // Zoom out
    document.getElementById('zoom-out').addEventListener('click', () => {
        if (zoom > 50) {
            zoom -= 10;
            zoomLevel.textContent = zoom + '%';
            bookContent.style.transform = `scale(${zoom / 100})`;
            bookContent.style.transformOrigin = 'top center';
        }
    });

    // Zoom in
    document.getElementById('zoom-in').addEventListener('click', () => {
        if (zoom < 200) {
            zoom += 10;
            zoomLevel.textContent = zoom + '%';
            bookContent.style.transform = `scale(${zoom / 100})`;
            bookContent.style.transformOrigin = 'top center';
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            document.getElementById('prev-page').click();
        } else if (e.key === 'ArrowRight') {
            document.getElementById('next-page').click();
        }
    });
</script>
@endpush
