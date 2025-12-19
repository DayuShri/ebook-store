<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - eBook Store</title>
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
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .user-info {
            font-size: 14px;
            color: #4a5568;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .stat-label {
            font-size: 14px;
            color: #718096;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 4px;
        }
        
        .stat-change {
            font-size: 12px;
            color: #48bb78;
        }
        
        .section {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 16px;
        }
        
        .reading-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .reading-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f7fafc;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .reading-item:hover {
            background: #edf2f7;
        }
        
        .reading-info {
            flex: 1;
        }
        
        .reading-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 4px;
        }
        
        .reading-meta {
            font-size: 12px;
            color: #718096;
        }
        
        .reading-progress {
            width: 200px;
        }
        
        .progress-bar-small {
            width: 100%;
            height: 4px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 4px;
        }
        
        .progress-fill-small {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        
        .progress-text-small {
            font-size: 12px;
            color: #718096;
            text-align: right;
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
        
        .empty-message {
            text-align: center;
            padding: 40px;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📊 Dashboard</h1>
            <div class="nav-menu">
                <a href="/dashboard" class="nav-link active">Dashboard</a>
                <a href="/library" class="nav-link">Library</a>
                <a href="/reviews" class="nav-link">My Reviews</a>
            </div>
            <div class="user-menu">
                <span class="user-info" id="userInfo">Loading...</span>
                <button class="btn-logout" onclick="logout()">Logout</button>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div id="loading" class="loading">
            <div class="spinner-large"></div>
            <p>Loading your dashboard...</p>
        </div>
        
        <div id="dashboard" style="display: none;">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Books</div>
                    <div class="stat-value" id="totalBooks">0</div>
                    <div class="stat-change">in your library</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-label">Books in Progress</div>
                    <div class="stat-value" id="booksInProgress">0</div>
                    <div class="stat-change">currently reading</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-label">Books Completed</div>
                    <div class="stat-value" id="booksCompleted">0</div>
                    <div class="stat-change">finished reading</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-label">Overall Progress</div>
                    <div class="stat-value" id="overallProgress">0%</div>
                    <div class="stat-change">average completion</div>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">📖 Continue Reading</div>
                <div id="continueReading" class="reading-list"></div>
            </div>
            
            <div class="section">
                <div class="section-title">✅ Recently Completed</div>
                <div id="recentlyCompleted" class="reading-list"></div>
            </div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('auth_token');
        const userName = localStorage.getItem('user_name');
        const userEmail = localStorage.getItem('user_email');
        
        console.log('Dashboard page loaded');
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
        
        function openReader(bookId) {
            window.location.href = `/reader/${bookId}`;
        }
        
        async function loadDashboard() {
            try {
                console.log('Fetching library with token:', token);
                
                // Load library
                const libraryResponse = await fetch('/api/v1/library', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                
                console.log('Library response status:', libraryResponse.status);
                
                if (libraryResponse.status === 401) {
                    console.error('Unauthorized - Token might be invalid');
                    const errorData = await libraryResponse.json();
                    console.error('Error details:', errorData);
                    
                    // Clear token and redirect
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user_name');
                    localStorage.removeItem('user_email');
                    window.location.href = '/login';
                    return;
                }
                
                if (!libraryResponse.ok) {
                    const errorText = await libraryResponse.text();
                    console.error('Error response:', errorText);
                    throw new Error('Failed to load library');
                }
                
                const libraryData = await libraryResponse.json();
                const books = libraryData.data || [];
                
                // Load progress for all books
                const booksWithProgress = await Promise.all(
                    books.map(async (book) => {
                        try {
                            const progressResponse = await fetch(`/api/v1/reading/progress/${book.book_id}`, {
                                headers: {
                                    'Authorization': `Bearer ${token}`,
                                    'Accept': 'application/json'
                                }
                            });
                            
                            if (progressResponse.ok) {
                                const progressData = await progressResponse.json();
                                book.progress = progressData.data || null;
                            }
                        } catch (error) {
                            console.error(`Error loading progress for book ${book.book_id}:`, error);
                        }
                        return book;
                    })
                );
                
                displayDashboard(booksWithProgress);
                
            } catch (error) {
                console.error('Error loading dashboard:', error);
                document.getElementById('loading').innerHTML = '<p>Failed to load dashboard. Please try again.</p>';
            }
        }
        
        function displayDashboard(books) {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('dashboard').style.display = 'block';
            
            // Calculate statistics
            const totalBooks = books.length;
            const booksWithProgress = books.filter(b => b.progress && b.progress.progress_percentage > 0 && b.progress.progress_percentage < 100);
            const completedBooks = books.filter(b => b.progress && b.progress.progress_percentage >= 100);
            const avgProgress = books.length > 0 
                ? Math.round(books.reduce((sum, b) => sum + (b.progress?.progress_percentage || 0), 0) / books.length)
                : 0;
            
            // Update stats
            document.getElementById('totalBooks').textContent = totalBooks;
            document.getElementById('booksInProgress').textContent = booksWithProgress.length;
            document.getElementById('booksCompleted').textContent = completedBooks.length;
            document.getElementById('overallProgress').textContent = avgProgress + '%';
            
            // Display continue reading
            const continueReadingDiv = document.getElementById('continueReading');
            if (booksWithProgress.length === 0) {
                continueReadingDiv.innerHTML = '<div class="empty-message">No books in progress. Start reading from your library!</div>';
            } else {
                continueReadingDiv.innerHTML = booksWithProgress.map(book => `
                    <div class="reading-item" onclick="openReader(${book.book_id})">
                        <div class="reading-info">
                            <div class="reading-title">${escapeHtml(book.book?.title || 'Untitled')}</div>
                            <div class="reading-meta">by ${escapeHtml(book.book?.author || 'Unknown')}</div>
                        </div>
                        <div class="reading-progress">
                            <div class="progress-bar-small">
                                <div class="progress-fill-small" style="width: ${book.progress.progress_percentage}%"></div>
                            </div>
                            <div class="progress-text-small">
                                ${Math.round(book.progress.progress_percentage)}% • Page ${book.progress.current_page}/${book.progress.total_pages}
                            </div>
                        </div>
                    </div>
                `).join('');
            }
            
            // Display recently completed
            const recentlyCompletedDiv = document.getElementById('recentlyCompleted');
            if (completedBooks.length === 0) {
                recentlyCompletedDiv.innerHTML = '<div class="empty-message">No completed books yet. Keep reading!</div>';
            } else {
                recentlyCompletedDiv.innerHTML = completedBooks.slice(0, 5).map(book => `
                    <div class="reading-item" onclick="openReader(${book.book_id})">
                        <div class="reading-info">
                            <div class="reading-title">${escapeHtml(book.book?.title || 'Untitled')}</div>
                            <div class="reading-meta">by ${escapeHtml(book.book?.author || 'Unknown')}</div>
                        </div>
                        <div class="reading-progress">
                            <div class="progress-bar-small">
                                <div class="progress-fill-small" style="width: 100%"></div>
                            </div>
                            <div class="progress-text-small">✓ Completed</div>
                        </div>
                    </div>
                `).join('');
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        loadDashboard();
    </script>
</body>
</html>
