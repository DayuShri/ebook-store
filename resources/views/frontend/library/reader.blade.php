@extends('frontend.layouts.app')

@section('title', $book['title'] . ' - Reader')

@section('content')
<div class="min-h-screen bg-gray-900 text-white">
    {{-- HEADER --}}
    <div class="fixed top-0 left-0 right-0 bg-gray-800 shadow-lg z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('library.index') }}"
               class="flex items-center space-x-2 text-gray-300 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 19l-7-7 7-7"></path>
                </svg>
                <span>Kembali</span>
            </a>

            <h1 class="text-lg font-semibold truncate">{{ $book['title'] }}</h1>

            <span id="progress-display">{{ number_format($progress->progress_percentage, 0) }}%</span>
        </div>
    </div>

    {{-- READER --}}
    <div class="pt-24 pb-8 px-4">
        <div class="max-w-5xl mx-auto bg-white rounded-lg shadow-2xl min-h-screen">
            <canvas id="pdf-canvas" class="mx-auto hidden"></canvas>

            <div id="loading" class="text-center py-20 text-gray-600">
                Loading book...
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
/* ==============================
   CONFIG
================================ */
const STREAM_URL = @json($streamUrl);
const BOOK_ID = @json($bookId);
const INITIAL_PAGE = {{ $progress->last_page_read ?? 1 }};
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const VIEWER_TOKEN = sessionStorage.getItem('viewer_token');

let pdfDoc = null;
let currentPage = INITIAL_PAGE;
let totalPages = 0;
let zoom = 1.2;
let progressTimer = null;

/* ==============================
   VALIDATE TOKEN
================================ */
if (!VIEWER_TOKEN) {
    alert('Viewer token missing');
    window.location.href = "{{ route('library.index') }}";
}

/* ==============================
   START READING
================================ */
async function startReading() {
    await fetch('/api/reading/start', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Viewer-Token': VIEWER_TOKEN,
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({
            book_id: BOOK_ID,
            device_info: navigator.userAgent
        })
    });
}

/* ==============================
   LOAD PDF
================================ */
async function loadPDF() {
    const loadingTask = pdfjsLib.getDocument(STREAM_URL);
    pdfDoc = await loadingTask.promise;

    totalPages = pdfDoc.numPages;
    document.getElementById('loading').classList.add('hidden');
    document.getElementById('pdf-canvas').classList.remove('hidden');

    await renderPage(currentPage);
}

/* ==============================
   RENDER PAGE
================================ */
async function renderPage(page) {
    if (page < 1 || page > totalPages) return;

    const pdfPage = await pdfDoc.getPage(page);
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');

    const viewport = pdfPage.getViewport({ scale: zoom });
    canvas.width = viewport.width;
    canvas.height = viewport.height;

    await pdfPage.render({ canvasContext: ctx, viewport }).promise;

    currentPage = page;
    updateUI();
    debounceProgress();
}

/* ==============================
   UI UPDATE
================================ */
function updateUI() {
    const percent = Math.round((currentPage / totalPages) * 100);
    document.getElementById('progress-display').textContent = percent + '%';
}

/* ==============================
   UPDATE PROGRESS
================================ */
function debounceProgress() {
    clearTimeout(progressTimer);

    progressTimer = setTimeout(updateProgress, 3000);
}

async function updateProgress() {
    await fetch('/api/reading/progress', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Viewer-Token': VIEWER_TOKEN,
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({
            book_id: BOOK_ID,
            current_page: currentPage
        })
    });
}

/* ==============================
   FINISH READING
================================ */
async function finishReading() {
    await fetch('/api/reading/finish', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Viewer-Token': VIEWER_TOKEN,
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({
            book_id: BOOK_ID,
            last_page_read: currentPage
        })
    });
}

/* ==============================
   EVENTS
================================ */
document.addEventListener('keydown', e => {
    if (e.key === 'ArrowRight') renderPage(currentPage + 1);
    if (e.key === 'ArrowLeft') renderPage(currentPage - 1);
});

window.addEventListener('beforeunload', finishReading);

/* ==============================
   INIT
================================ */
(async () => {
    try {
        await startReading();
        await loadPDF();
    } catch (e) {
        alert('Gagal membuka buku');
        console.error(e);
    }
})();
</script>
@endsection
