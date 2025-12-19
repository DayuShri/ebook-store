@extends('frontend.layouts.app')

@section('title', $book['title'] . ' - Reader')

@section('content')
<div class="min-h-screen bg-gray-900 text-white">
    {{-- Header Controls --}}
    <div class="fixed top-0 left-0 right-0 bg-gray-800 shadow-lg z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('library.index') }}" 
               class="flex items-center space-x-2 text-gray-300 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span>Kembali</span>
            </a>

            <div class="flex-1 text-center px-4">
                <h1 class="text-lg font-semibold truncate">{{ $book['title'] }}</h1>
            </div>

            <div class="text-sm text-gray-400">
                <span id="progress-display">{{ number_format($progress->progress_percentage, 0) }}%</span>
            </div>
        </div>

        <div class="border-t border-gray-700 bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-center space-x-4">
                <button id="prev-page" class="p-2 hover:bg-gray-700 rounded transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <span id="page-info" class="text-sm text-gray-400 min-w-[100px] text-center">
                    Loading...
                </span>

                <button id="next-page" class="p-2 hover:bg-gray-700 rounded transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <div class="w-px h-6 bg-gray-700"></div>

                <button id="zoom-out" class="p-2 hover:bg-gray-700 rounded transition-colors" title="Zoom Out">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path>
                    </svg>
                </button>

                <span id="zoom-display" class="text-sm text-gray-400 min-w-[60px] text-center">100%</span>

                <button id="zoom-in" class="p-2 hover:bg-gray-700 rounded transition-colors" title="Zoom In">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Reader Container --}}
    <div class="pt-32 pb-8 px-4">
        <div id="reader-container" class="max-w-5xl mx-auto bg-white rounded-lg shadow-2xl min-h-screen">
            <div id="pdf-viewer" class="hidden">
                <canvas id="pdf-canvas" class="mx-auto"></canvas>
            </div>

            <div id="epub-viewer" class="hidden" style="height: 800px;"></div>

            <div id="loading-state" class="flex flex-col items-center justify-center py-20">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mb-4"></div>
                <p class="text-gray-600">Loading book...</p>
            </div>

            <div id="error-state" class="hidden flex flex-col items-center justify-center py-20">
                <svg class="w-16 h-16 text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-600 mb-2">Failed to load book</p>
                <p id="error-message" class="text-sm text-gray-500"></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://unpkg.com/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/epubjs/dist/epub.min.js"></script>

<script>
const STREAM_URL = @json($streamUrl);
const BOOK_ID = @json($bookId);
const INITIAL_PAGE = {{ $progress->last_page_read ?? 1 }};
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

let currentPage = INITIAL_PAGE;
let totalPages = {{ $progress->total_pages ?? 200 }};
let zoom = 1.0;
let book = null;
let rendition = null;
let pdfDoc = null;

async function detectFormat() {
    try {
        // Fetch the stream URL
        const response = await fetch(STREAM_URL);
        
        // Check if it's JSON response (EPUB)
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            const data = await response.json();
            if (data.format === 'epub' && data.url) {
                console.log('Book format detected: epub');
                // Fetch EPUB file as ArrayBuffer for ePub.js
                const epubResponse = await fetch(data.url);
                const epubBlob = await epubResponse.blob();
                book = ePub(epubBlob);
                return 'epub';
            }
        }
        
        // If not JSON, it's a redirect to PDF
        console.log('Book format detected: pdf');
        const loadingTask = pdfjsLib.getDocument(STREAM_URL);
        pdfDoc = await loadingTask.promise;
        return 'pdf';
    } catch (error) {
        console.error('Format detection failed:', error);
        throw new Error('Failed to load book file');
    }
}

async function initPDFReader() {
    document.getElementById('pdf-viewer').classList.remove('hidden');
    document.getElementById('loading-state').classList.add('hidden');
    
    totalPages = pdfDoc.numPages;
    await renderPage(currentPage);
    
    updatePageInfo();
    enableControls();
}

async function renderPage(pageNum) {
    if (!pdfDoc || pageNum < 1 || pageNum > totalPages) return;
    
    const page = await pdfDoc.getPage(pageNum);
    const canvas = document.getElementById('pdf-canvas');
    const context = canvas.getContext('2d');
    
    const viewport = page.getViewport({ scale: zoom * 1.5 });
    canvas.width = viewport.width;
    canvas.height = viewport.height;
    
    await page.render({
        canvasContext: context,
        viewport: viewport
    }).promise;
    
    currentPage = pageNum;
    updatePageInfo();
    updateProgress();
}

async function initEPUBReader() {
    document.getElementById('epub-viewer').classList.remove('hidden');
    document.getElementById('loading-state').classList.add('hidden');
    
    rendition = book.renderTo('epub-viewer', {
        width: '100%',
        height: '100%',
        spread: 'none',
        allowScriptedContent: false
    });
    
    await rendition.display();
    
    book.ready.then(() => {
        return book.locations.generate(1600);
    }).then(() => {
        totalPages = book.locations.total;
        updatePageInfo();
    });
    
    rendition.on('relocated', (location) => {
        if (book.locations.total > 0) {
            const percentage = book.locations.percentageFromCfi(location.start.cfi);
            currentPage = Math.ceil(percentage * totalPages);
            
            // Always update UI immediately for smooth experience
            updatePageInfo();
            
            // Debounce server update to avoid too many requests
            updateProgress();
        }
    });
    
    enableControls();
}

function enableControls() {
    document.getElementById('prev-page').onclick = () => {
        if (pdfDoc) {
            if (currentPage > 1) renderPage(currentPage - 1);
        } else if (rendition) {
            rendition.prev();
        }
    };
    
    document.getElementById('next-page').onclick = () => {
        if (pdfDoc) {
            if (currentPage < totalPages) renderPage(currentPage + 1);
        } else if (rendition) {
            rendition.next();
        }
    };
    
    document.getElementById('zoom-in').onclick = () => {
        zoom = Math.min(zoom + 0.1, 3.0);
        updateZoom();
    };
    
    document.getElementById('zoom-out').onclick = () => {
        zoom = Math.max(zoom - 0.1, 0.5);
        updateZoom();
    };
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') document.getElementById('prev-page').click();
        if (e.key === 'ArrowRight') document.getElementById('next-page').click();
        if (e.key === '+' || e.key === '=') document.getElementById('zoom-in').click();
        if (e.key === '-') document.getElementById('zoom-out').click();
    });
}

function updateZoom() {
    document.getElementById('zoom-display').textContent = `${Math.round(zoom * 100)}%`;
    
    if (pdfDoc) {
        renderPage(currentPage);
    } else if (rendition) {
        const fontSize = 100 * zoom;
        rendition.themes.fontSize(`${fontSize}%`);
    }
}

function updatePageInfo() {
    document.getElementById('page-info').textContent = `${currentPage} / ${totalPages}`;
    
    const percentage = Math.round((currentPage / totalPages) * 100);
    document.getElementById('progress-display').textContent = `${percentage}%`;
}

let progressUpdateTimeout;
function updateProgress() {
    clearTimeout(progressUpdateTimeout);
    
    progressUpdateTimeout = setTimeout(async () => {
        try {
            console.log('Updating progress:', {
                book_id: BOOK_ID,
                page: currentPage,
                total: totalPages
            });
            
            const response = await fetch(`/library/progress/${BOOK_ID}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    last_page_read: currentPage,
                    total_pages: totalPages
                })
            });
            
            if (response.ok) {
                console.log('Progress saved successfully');
            } else {
                console.error('Failed to save progress:', await response.text());
            }
        } catch (error) {
            console.error('Failed to update progress:', error);
        }
    }, 3000); // 3 seconds debounce
}

function showError(message) {
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('error-state').classList.remove('hidden');
    document.getElementById('error-message').textContent = message;
}

(async function() {
    try {
        const format = await detectFormat();
        console.log('Book format detected:', format);
        
        if (format === 'pdf') {
            await initPDFReader();
        } else if (format === 'epub') {
            await initEPUBReader();
        }
    } catch (error) {
        console.error('Reader initialization failed:', error);
        showError(error.message || 'Unknown error occurred');
    }
})();

window.addEventListener('beforeunload', () => {
    updateProgress();
});
</script>
@endsection
