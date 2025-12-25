<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Library - eBook Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f7fafc;
            min-height: 100vh;
        }
        
        .header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
            color: #1a202c;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .user-info {
            font-size: 14px;
            color: #4a5568;
        }
        
        .nav-menu {
            display: flex;
            gap: 16px;
        }
        
        .nav-link {
            padding: 8px 16px;
            color: #4a5568;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s;
        }
        
        .nav-link:hover {
            background: #edf2f7;
        }
        
        .nav-link.active {
            background: #667eea;
            color: white;
        }
        
        .btn-logout {
            padding: 8px 16px;
            background: #e53e3e;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        .btn-logout:hover {
            background: #c53030;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 24px;
        }
        
        .loading {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }
        
        .spinner-large {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #e2e8f0;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-bottom: 16px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state h2 {
            color: #2d3748;
            margin-bottom: 8px;
        }
        
        .empty-state p {
            color: #718096;
        }
        
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }
        
        .book-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        
        .book-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        
        .book-cover {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
        }
        
        .book-info {
            padding: 16px;
        }
        
        .book-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .book-author {
            font-size: 14px;
            color: #718096;
            margin-bottom: 12px;
        }
        
        .progress-container {
            margin-bottom: 12px;
        }
        
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 6px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
        }
        
        .progress-text {
            font-size: 12px;
            color: #718096;
            display: flex;
            justify-content: space-between;
        }
        
        .book-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #a0aec0;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
        }
        
        .book-format {
            background: #edf2f7;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .error-message {
            background: #fee;
            color: #c53030;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            border: 1px solid #fc8181;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📚 My Library</h1>
            <div class="nav-menu">
                <a href="/dashboard" class="nav-link">Dashboard</a>
                <a href="/library" class="nav-link active">Library</a>
                <a href="/reviews" class="nav-link">My Reviews</a>
            </div>
            <div class="user-menu">
                <span class="user-info" id="userInfo">Loading...</span>
                <button class="btn-logout" onclick="logout()">Logout</button>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div id="errorMessage" class="error-message" style="display: none;"></div>
        
        <div id="loading" class="loading">
            <div class="spinner-large"></div>
            <p>Loading your library...</p>
        </div>
        
        <div id="emptyState" class="empty-state" style="display: none;">
            <h2>Your library is empty</h2>
            <p>You haven't added any books to your library yet.</p>
        </div>
        
        <div id="booksGrid" class="books-grid" style="display: none;"></div>
    </div>

    <script>
        const token = localStorage.getItem('auth_token');
        const userName = localStorage.getItem('user_name');
        const userEmail = localStorage.getItem('user_email');
        
        console.log('Library page loaded');
        console.log('Token from localStorage:', token);
        
        
        if (!token) {
            console.log('No token found, redirecting to login');
            window.location.href = '/login';
        }
        
        
        document.getElementById('userInfo').textContent = userName || userEmail || 'User';
        
        function logout() {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_name');
            localStorage.removeItem('user_email');
            window.location.href = '/login';
        }
        
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            document.getElementById('loading').style.display = 'none';
        }
        

        async function readingStart(bookId) {
            const viewerToken = localStorage.getItem('viewer_token');

            await fetch('/api/v1/reading/start', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-Viewer-Token': viewerToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    book_id: bookId,
                    device_info: navigator.userAgent
                })
            });
        }

        async function readingProgress(bookId, currentPage) {
            const viewerToken = localStorage.getItem('viewer_token');

            await fetch('/api/v1/reading/progress', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-Viewer-Token': viewerToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    book_id: bookId,
                    current_page: currentPage
                })
            });
        }

        async function readingFinish(bookId, lastPage) {
            const viewerToken = localStorage.getItem('viewer_token');

            await fetch('/api/v1/reading/finish', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-Viewer-Token': viewerToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    book_id: bookId,
                    last_page_read: lastPage
                })
            });
        }



        async function openReader(bookId) {
            console.log('Opening reader for book:', bookId);

            try {
                const response = await fetch('/api/v1/library/viewer', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        book_id: bookId,
                        format: 'pdf'
                    })
                });

                if (!response.ok) {
                    const err = await response.json();
                    alert(err.message || 'Failed to create viewer session');
                    return;
                }

                const result = await response.json();

                
                localStorage.setItem('viewer_token', result.data.token);
                localStorage.setItem('viewer_expired_at', result.data.expires_at);

                
                await readingStart(bookId);

                
                window.location.href = `/reader/${bookId}`;

            } catch (error) {
                console.error('Failed to open reader', error);
                alert('Failed to open reader');
            }
        }

        
        function getDeviceInfo() {
            return {
                userAgent: navigator.userAgent,
                platform: navigator.platform,
                language: navigator.language,
                screen: `${window.screen.width}x${window.screen.height}`,
            };
        }

        async function loadLibrary() {
            try {
                console.log('Fetching library with token:', token);
                
                const response = await fetch('/api/v1/library', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                
                console.log('Response status:', response.status);
                
                if (response.status === 401) {
                    
                    console.error('Unauthorized - Token might be invalid');
                    const errorData = await response.json();
                    console.error('Error details:', errorData);
                    
                    localStorage.removeItem('auth_token');
                    window.location.href = '/login';
                    return;
                }
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Error response:', errorText);
                    throw new Error('Failed to load library');
                }

                const data = await response.json();
                console.log('Library data:', data);
                const books = data.data || [];
                
                document.getElementById('loading').style.display = 'none';
                
                if (books.length === 0) {
                    document.getElementById('emptyState').style.display = 'block';
                } else {
                    
                    await loadBooksWithProgress(books);
                }
            } catch (error) {
                console.error('Error loading library:', error);
                showError('Failed to load your library. Please try again.');
            }
        }
        
        async function loadBooksWithProgress(books) {
            
            const booksWithProgress = await Promise.all(
                books.map(async (book) => {
                    const viewerToken = localStorage.getItem('viewer_token');
                    try {
                        const deviceInfo = getDeviceInfo();
                        const progressResponse = await fetch(`/api/v1/reading/progress/${book.book_id}`, {
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'X-Viewer-Token': viewerToken,
                                'Accept': 'application/json'
                            }
                        });
                        
                        
                        if (progressResponse.ok) {
                            const progressData = await progressResponse.json();
                                book.progress = {
                                last_page_read: Number(progressData.data.last_page_read ?? 0),
                                total_pages: Number(progressData.data.total_pages ?? 0),
                                progress_percentage: Number(progressData.data.progress_percentage ?? 0),
                                last_read_at: progressData.data.last_read_at,
                                device_info: progressData.data.device_info
                            };
                        } else if (progressResponse.status === 404) {
                            console.log(`No progress found for book ${book.book_id} - not started yet`);
                            book.progress = null;
                        }
                    } catch (error) {
                        console.error(`Error loading progress for book ${book.book_id}:`, error);
                        book.progress = null;
                    }
                    return book;
                })
            );
            
            displayBooks(booksWithProgress);
        }
        
        function displayBooks(books) {
            const grid = document.getElementById('booksGrid');
            grid.style.display = 'grid';

            grid.innerHTML = books.map(item => {
                const progress = item.progress || {};
                const progressPercent = Number(progress.progress_percentage ?? 0);
                const currentPage = Number(progress.last_page_read ?? 0);
                const totalPages = Number(progress.total_pages ?? 0);
                const bookId = item.book_id || item.id;

                return `
                    <div class="book-card" data-book-id="${bookId}">
                        <div class="book-cover">📖</div>
                        <div class="book-info">
                            <div class="book-title">${escapeHtml(item.book?.title || 'Untitled')}</div>
                            <div class="book-author">${escapeHtml(item.book?.author || 'Unknown Author')}</div>

                            <div class="progress-container">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: ${progressPercent}%"></div>
                                </div>
                                <div class="progress-text">
                                    <span>${Math.round(progressPercent)}% Complete</span>
                                    <span>Page ${currentPage} / ${totalPages || '?'}</span>
                                </div>
                            </div>

                            <div class="book-meta">
                                <span class="book-format">${item.formats?.join(', ') || 'PDF'}</span>
                                <span>${formatDate(item.purchased_at)}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            document.querySelectorAll('.book-card').forEach(card => {
                card.addEventListener('click', function () {
                    openReader(this.dataset.bookId);
                });
            });
        }

        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }
        
        
        loadLibrary();
    </script>
</body>
</html>
