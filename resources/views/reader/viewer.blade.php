<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $bookTitle ?? 'E-Book Reader' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a1a;
            color: #fff;
            overflow: hidden;
        }

        .reader-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .reader-header {
            background: #2d2d2d;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .book-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-btn {
            background: #444;
            border: none;
            color: #fff;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: #555;
        }

        .book-title {
            font-size: 18px;
            font-weight: 600;
        }

        .progress-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .page-counter {
            font-size: 14px;
            color: #aaa;
            font-weight: 500;
        }

        .progress-bar {
            width: 200px;
            height: 10px;
            background: #444;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.3);
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            width: 0%;
            transition: width 0.5s ease;
            box-shadow: 0 0 10px rgba(76, 175, 80, 0.5);
        }

        .progress-percent {
            font-size: 16px;
            color: #4CAF50;
            font-weight: 700;
            min-width: 50px;
            text-align: right;
        }

        .reader-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            overflow: hidden;
        }

        #pdf-viewer {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-y: auto;
        }

        #pdf-canvas {
            max-width: 100%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            margin-bottom: 20px;
        }

        .reader-controls {
            background: #2d2d2d;
            padding: 15px 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
        }

        .control-btn {
            background: #444;
            border: none;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        .control-btn:hover:not(:disabled) {
            background: #555;
        }

        .control-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-input {
            background: #444;
            border: 1px solid #555;
            color: #fff;
            padding: 8px 12px;
            border-radius: 5px;
            width: 80px;
            text-align: center;
        }

        .loading {
            text-align: center;
            padding: 50px;
        }

        .spinner {
            border: 3px solid #444;
            border-top: 3px solid #4CAF50;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error-message {
            background: #d32f2f;
            color: #fff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px;
            text-align: center;
        }

        .zoom-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .zoom-btn {
            background: #444;
            border: none;
            color: #fff;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .zoom-btn:hover {
            background: #555;
        }

        /* Review Modal */
        .review-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .review-content {
            background: #2d2d2d;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.5);
        }

        .review-content h2 {
            margin-bottom: 20px;
            color: #fff;
        }

        .rating-container {
            margin: 20px 0;
        }

        .stars {
            display: flex;
            gap: 10px;
            font-size: 32px;
            margin: 10px 0;
        }

        .star {
            cursor: pointer;
            color: #444;
            transition: color 0.2s;
        }

        .star:hover,
        .star.active {
            color: #FFD700;
        }

        .review-textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            border: 1px solid #444;
            border-radius: 5px;
            background: #1a1a1a;
            color: #fff;
            font-family: inherit;
            resize: vertical;
        }

        .review-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .review-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        .submit-review {
            background: #4CAF50;
            color: white;
        }

        .submit-review:hover {
            background: #45a049;
        }

        .skip-review {
            background: #666;
            color: white;
        }

        .skip-review:hover {
            background: #777;
        }

        .completion-badge {
            background: linear-gradient(135deg, #4CAF50, #8BC34A);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .completion-badge h3 {
            margin: 0;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="reader-container">
        <!-- Header -->
        <div class="reader-header">
            <div class="book-info">
                <button class="back-btn" onclick="goBack()">← Back to Library</button>
                <span class="book-title" id="book-title">{{ $bookTitle ?? 'Loading...' }}</span>
            </div>
            <div class="progress-info">
                <span class="page-counter" id="page-counter">Page <span id="current-page">0</span> of <span id="total-pages">0</span></span>
                <div class="progress-bar">
                    <div class="progress-fill" id="progress-fill"></div>
                </div>
                <span class="progress-percent" id="progress-percent">0%</span>
            </div>
        </div>

        <!-- Content Area -->
        <div class="reader-content">
            <div id="loading" class="loading">
                <div class="spinner"></div>
                <p>Loading your book...</p>
            </div>
            <div id="error" class="error-message" style="display: none;"></div>
            <div id="pdf-viewer" style="display: none;">
                <canvas id="pdf-canvas"></canvas>
            </div>
        </div>

        <!-- Controls -->
        <div class="reader-controls">
            <button class="control-btn" id="prev-btn" onclick="previousPage()" disabled>← Previous</button>
            
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="color: #aaa;">Go to page:</span>
                <input type="number" class="page-input" id="page-input" min="1" value="1" 
                       onchange="goToPage(this.value)">
            </div>

            <button class="control-btn" id="next-btn" onclick="nextPage()" disabled>Next →</button>

            <div class="zoom-controls">
                <button class="zoom-btn" onclick="zoomOut()">−</button>
                <span id="zoom-level" style="color: #aaa; width: 60px; text-align: center;">100%</span>
                <button class="zoom-btn" onclick="zoomIn()">+</button>
            </div>
        </div>
    </div>

    <!-- PDF.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Configuration
        const BOOK_ID = '{{ $bookId }}';
        const FILE_FORMAT = '{{ $fileFormat }}';
        const API_BASE = '{{ url("/api/v1") }}';
        const AUTH_TOKEN = localStorage.getItem('auth_token');
        
        // Check authentication
        if (!AUTH_TOKEN) {
            console.error('No auth token found');
            showError('Please login to continue');
            setTimeout(() => {
                window.location.href = '/login';
            }, 2000);
        }
        
        // PDF.js setup
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        // State
        let pdfDoc = null;
        let currentPage = 1;
        let totalPages = 0;
        let scale = 1.5;
        let viewerToken = null;
        let progressUpdateTimer = null;
        let sessionStarted = false;
        let selectedRating = 0;
        let bookCompleted = false;

        // Star rating functionality
        document.addEventListener('DOMContentLoaded', () => {
            const stars = document.querySelectorAll('.star');
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    selectedRating = parseInt(this.dataset.rating);
                    updateStarDisplay();
                });
            });
        });

        function updateStarDisplay() {
            const stars = document.querySelectorAll('.star');
            stars.forEach(star => {
                const rating = parseInt(star.dataset.rating);
                if (rating <= selectedRating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        // Initialize - only if we have auth token
        if (AUTH_TOKEN) {
            document.addEventListener('DOMContentLoaded', async () => {
            console.log('=== Reader Initialization ===');
            console.log('Book ID:', BOOK_ID);
            console.log('Auth token:', AUTH_TOKEN ? 'Present' : 'Missing');
            
            try {
                // ✅ STEP 1: Create viewer session (validates grant/access)
                console.log('STEP 1: Creating viewer session & validating access...');
                await createViewerSession();
                console.log('✓ Access granted! Viewer session created.');
                
                // ✅ STEP 2: Start reading session (for progress tracking)
                console.log('STEP 2: Starting reading session for progress tracking...');
                await startReadingSession();
                console.log('✓ Reading session started.');
                
                // ✅ STEP 3: Load the PDF from Supabase
                console.log('STEP 3: Loading PDF from Supabase...');
                await loadPDF();
                console.log('✓ PDF loaded successfully!');
                
                console.log('=== Reader Ready ===');
            } catch (error) {
                console.error('❌ Failed to load book:', error);
                showError('Failed to load book: ' + error.message);
            }
        });

        // Start reading session (for progress tracking)
        async function startReadingSession() {
            try {
                const response = await fetch(`${API_BASE}/reading/start`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${AUTH_TOKEN}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        book_id: BOOK_ID,
                        device_info: navigator.userAgent
                    })
                });

                if (!response.ok) throw new Error('Failed to start reading session');
                
                sessionStarted = true;
                console.log('Reading session started');
            } catch (error) {
                console.error('Start session error:', error);
            }
        }

        // Create viewer session to get stream URL
        async function createViewerSession() {
            console.log('Requesting viewer session for book:', BOOK_ID);
            
            try {
                const response = await fetch(`${API_BASE}/library/viewer`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${AUTH_TOKEN}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        book_id: BOOK_ID,
                        format: FILE_FORMAT
                    })
                });

                console.log('Viewer session response status:', response.status);

                if (!response.ok) {
                    const error = await response.json();
                    console.error('Viewer session error:', error);
                    throw new Error(error.message || 'Failed to create viewer session');
                }

                const data = await response.json();
                console.log('Viewer session created:', data);
                viewerToken = data.data.token;
                return data.data.stream_url;
            } catch (error) {
                console.error('Create viewer session error:', error);
                throw new Error('Cannot access book: ' + error.message);
            }
        }
        // Load PDF from Supabase
        async function loadPDF() {
            if (!viewerToken) throw new Error('No viewer token');

            const streamUrl = `${API_BASE}/library/stream/${viewerToken}`;
            
            // Fetch the PDF file
            const response = await fetch(streamUrl);
            if (!response.ok) throw new Error('Failed to fetch PDF file');

            const pdfData = await response.arrayBuffer();
            
            // Load PDF with PDF.js
            const loadingTask = pdfjsLib.getDocument({ data: pdfData });
            pdfDoc = await loadingTask.promise;
            
            totalPages = pdfDoc.numPages;
            
            // Update UI
            document.getElementById('total-pages').textContent = totalPages;
            document.getElementById('page-input').max = totalPages;
            
            // Load saved progress
            await loadProgress();
            
            // Render first page
            await renderPage(currentPage);
            
            // Show viewer
            document.getElementById('loading').style.display = 'none';
            document.getElementById('pdf-viewer').style.display = 'flex';
        }

        // Load saved progress from server
        async function loadProgress() {
            try {
                const response = await fetch(`${API_BASE}/reading/progress/${BOOK_ID}`, {
                    headers: {
                        'Authorization': `Bearer ${AUTH_TOKEN}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const savedPage = data.data.last_page_read || 1;
                    currentPage = Math.min(savedPage, totalPages);
                    console.log(`Resuming from page ${currentPage}`);
                }
            } catch (error) {
                console.log('No saved progress, starting from page 1');
            }
        }

        // Render PDF page
        async function renderPage(pageNum) {
            const page = await pdfDoc.getPage(pageNum);
            const canvas = document.getElementById('pdf-canvas');
            const context = canvas.getContext('2d');
            
            const viewport = page.getViewport({ scale: scale });
            canvas.width = viewport.width;
            canvas.height = viewport.height;

            await page.render({
                canvasContext: context,
                viewport: viewport
            }).promise;

            // Update UI
            currentPage = pageNum;
            document.getElementById('current-page').textContent = currentPage;
            document.getElementById('page-input').value = currentPage;
            
            // Update progress
            updateProgress();
            
            // Update buttons
            document.getElementById('prev-btn').disabled = (currentPage <= 1);
            document.getElementById('next-btn').disabled = (currentPage >= totalPages);

            // Auto-save progress (debounced)
            scheduleProgressUpdate();
        }

        // Update progress display
        function updateProgress() {
            const percentage = Math.round((currentPage / totalPages) * 100);
            const progressFillEl = document.getElementById('progress-fill');
            const progressPercentEl = document.getElementById('progress-percent');
            
            if (progressFillEl) {
                progressFillEl.style.width = percentage + '%';
            }
            if (progressPercentEl) {
                progressPercentEl.textContent = percentage + '%';
            }
            
            console.log(`Progress updated: Page ${currentPage}/${totalPages} = ${percentage}%`);

            // Check if book is completed (reached last page)
            if (currentPage === totalPages && !bookCompleted) {
                bookCompleted = true;
                console.log('Book completed! Showing review modal...');
                // Show review modal after a short delay
                setTimeout(() => {
                    const modal = document.getElementById('review-modal');
                    if (modal) {
                        modal.style.display = 'flex';
                    }
                }, 1000);
            }
        }

        // Schedule progress update to server (debounced)
        function scheduleProgressUpdate() {
            clearTimeout(progressUpdateTimer);
            progressUpdateTimer = setTimeout(async () => {
                await saveProgressToServer();
            }, 3000); // Save after 3 seconds of inactivity
        }

        // Save progress to server
        async function saveProgressToServer() {
            if (!sessionStarted) return;

            const percentage = Math.round((currentPage / totalPages) * 100);
            
            try {
                const response = await fetch(`${API_BASE}/reading/progress`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${AUTH_TOKEN}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        book_id: BOOK_ID,
                        current_page: currentPage,
                        total_pages: totalPages,
                        progress_percentage: percentage
                    })
                });

                if (response.ok) {
                    console.log(`Progress saved: Page ${currentPage}/${totalPages} (${percentage}%)`);
                } else {
                    console.warn('Failed to save progress:', response.status);
                }
            } catch (error) {
                console.error('Failed to save progress:', error);
            }
        }

        // Navigation functions
        function previousPage() {
            if (currentPage > 1) {
                renderPage(currentPage - 1);
            }
        }

        function nextPage() {
            if (currentPage < totalPages) {
                renderPage(currentPage + 1);
            }
        }

        function goToPage(pageNum) {
            pageNum = parseInt(pageNum);
            if (pageNum >= 1 && pageNum <= totalPages) {
                renderPage(pageNum);
            }
        }

        // Zoom functions
        function zoomIn() {
            scale += 0.25;
            if (scale > 3) scale = 3;
            document.getElementById('zoom-level').textContent = Math.round(scale * 100) + '%';
            renderPage(currentPage);
        }

        function zoomOut() {
            scale -= 0.25;
            if (scale < 0.5) scale = 0.5;
            document.getElementById('zoom-level').textContent = Math.round(scale * 100) + '%';
            renderPage(currentPage);
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') previousPage();
            if (e.key === 'ArrowRight') nextPage();
        });

        // Save progress before leaving
        window.addEventListener('beforeunload', async (e) => {
            await saveProgressToServer();
            
            // Finish reading session
            if (sessionStarted) {
                navigator.sendBeacon(`${API_BASE}/reading/finish`, JSON.stringify({
                    book_id: BOOK_ID,
                    last_page_read: currentPage
                }));
            }
        });

        function goBack() {
            saveProgressToServer().then(() => {
                window.location.href = '{{ url("/library") }}';
            });
        }

        function showError(message) {
            document.getElementById('loading').style.display = 'none';
            const errorDiv = document.getElementById('error');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }

        // Review Modal Functions
        function showReviewModal() {
            document.getElementById('review-modal').style.display = 'flex';
        }

        function skipReview() {
            document.getElementById('review-modal').style.display = 'none';
        }

        async function submitReview() {
            if (selectedRating === 0) {
                alert('Please select a rating');
                return;
            }

            const reviewText = document.getElementById('review-text').value;

            try {
                const response = await fetch(`${API_BASE}/reading/review`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${AUTH_TOKEN}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        book_id: BOOK_ID,
                        rating: selectedRating,
                        review_text: reviewText || null
                    })
                });

                if (response.ok) {
                    alert('Thank you for your review!');
                    document.getElementById('review-modal').style.display = 'none';
                } else {
                    const data = await response.json();
                    alert('Failed to submit review: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error submitting review:', error);
                alert('Failed to submit review. Please try again.');
            }
        }
        } // End of if (AUTH_TOKEN)
    </script>
</body>
</html>
