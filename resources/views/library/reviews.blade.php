<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reviews - E-Book Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #667eea;
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-btn {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .nav-btn:hover {
            background: #5568d3;
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .review-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .book-info {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .book-cover {
            width: 80px;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
        }

        .book-details h3 {
            color: #333;
            margin-bottom: 5px;
            font-size: 18px;
        }

        .book-author {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stars {
            color: #FFD700;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .review-text {
            color: #444;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .review-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #999;
            font-size: 13px;
        }

        .review-date {
            font-style: italic;
        }

        .edit-btn, .delete-btn {
            padding: 5px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.3s;
        }

        .edit-btn {
            background: #4CAF50;
            color: white;
            margin-right: 5px;
        }

        .edit-btn:hover {
            background: #45a049;
        }

        .delete-btn {
            background: #f44336;
            color: white;
        }

        .delete-btn:hover {
            background: #da190b;
        }

        .empty-state {
            background: white;
            padding: 60px 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .empty-state h2 {
            color: #666;
            margin-bottom: 15px;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 25px;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 50px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-container {
            background: white;
            padding: 60px 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 My Reviews</h1>
            <div class="nav-links">
                <a href="/dashboard" class="nav-btn">Dashboard</a>
                <a href="/library" class="nav-btn">Library</a>
            </div>
        </div>

        <div id="loading" class="loading-container">
            <div class="spinner"></div>
            <p>Loading your reviews...</p>
        </div>

        <div id="reviews-container" style="display: none;">
            <div class="reviews-grid" id="reviews-grid"></div>
        </div>

        <div id="empty-state" class="empty-state" style="display: none;">
            <h2>No Reviews Yet</h2>
            <p>You haven't written any reviews yet. Finish reading a book and share your thoughts!</p>
            <a href="/library" class="nav-btn">Browse Library</a>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = '/login';
        }

        // Load reviews
        async function loadReviews() {
            try {
                const response = await fetch('/api/v1/reading/reviews', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to load reviews');
                }

                const data = await response.json();
                displayReviews(data.data);
            } catch (error) {
                console.error('Error loading reviews:', error);
                document.getElementById('loading').innerHTML = 
                    '<p style="color: #f44336;">Failed to load reviews. Please try again.</p>';
            }
        }

        function displayReviews(reviews) {
            document.getElementById('loading').style.display = 'none';

            if (!reviews || reviews.length === 0) {
                document.getElementById('empty-state').style.display = 'block';
                return;
            }

            document.getElementById('reviews-container').style.display = 'block';
            const grid = document.getElementById('reviews-grid');

            reviews.forEach(review => {
                const card = document.createElement('div');
                card.className = 'review-card';
                card.innerHTML = `
                    <div class="book-info">
                        ${review.book.cover_url ? 
                            `<img src="${review.book.cover_url}" alt="Book cover" class="book-cover">` :
                            `<div class="book-cover" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 40px;">📖</div>`
                        }
                        <div class="book-details">
                            <h3>${escapeHtml(review.book.title)}</h3>
                            <div class="book-author">${escapeHtml(review.book.author)}</div>
                            <div class="stars">${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}</div>
                        </div>
                    </div>
                    ${review.review_text ? 
                        `<div class="review-text">"${escapeHtml(review.review_text)}"</div>` :
                        '<div class="review-text" style="color: #999; font-style: italic;">No review text</div>'
                    }
                    <div class="review-meta">
                        <span class="review-date">${formatDate(review.created_at)}</span>
                        <div>
                            <button class="edit-btn" onclick="editReview('${review.id}')">Edit</button>
                            <button class="delete-btn" onclick="deleteReview('${review.id}')">Delete</button>
                        </div>
                    </div>
                `;
                grid.appendChild(card);
            });
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        async function deleteReview(reviewId) {
            if (!confirm('Are you sure you want to delete this review?')) {
                return;
            }

            try {
                const response = await fetch(`/api/v1/reading/review/${reviewId}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    alert('Review deleted successfully');
                    location.reload();
                } else {
                    alert('Failed to delete review');
                }
            } catch (error) {
                console.error('Error deleting review:', error);
                alert('Failed to delete review');
            }
        }

        function editReview(reviewId) {
            // TODO: Implement edit functionality
            alert('Edit functionality coming soon!');
        }

        // Load reviews on page load
        loadReviews();
    </script>
</body>
</html>
