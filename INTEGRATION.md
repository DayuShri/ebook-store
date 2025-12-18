# Library & Review_Reading Module Integration

## Overview
Library module dan Review_Reading module sekarang sudah terintegrasi. User harus punya akses buku di Library sebelum bisa membaca dan track progress.

## Integration Flow

```
1. Admin upload file buku → Library Module
   ↓
2. User beli/dapat buku → Library Module (status ACTIVE)
   ↓
3. User mulai baca → Review_Reading cek akses via Library
   ↓
4. User scroll buku → Update progress real-time
   ↓
5. User selesai baca → Simpan session
   ↓
6. User bisa review → Review Module
```

## Service Integration

### LibraryAccessService (Contract)
```php
interface LibraryAccessService {
    public function userHasBook(string $userId, string $bookId): bool;
}
```

### Implementation
- **Library Module**: `LibraryAccessServiceImpl` - Real check dari database
- **Review_Reading Module**: `ReadingService` - Inject contract via constructor

### Service Provider Binding
```php
// app/Providers/AppServiceProvider.php
$this->app->bind(
    \App\Modules\Review_Reading\Contracts\LibraryAccessService::class,
    \App\Modules\Library\Services\LibraryAccessServiceImpl::class
);
```

## API Endpoints

### Reading Progress Tracking

#### 1. Start Reading Session
```http
POST /api/v1/reading/start
Authorization: Bearer {user_token}
Content-Type: application/json

{
    "book_id": "{book_uuid}",
    "device_info": "Chrome/Windows"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Reading started",
    "data": null
}
```

**Validation:**
- ✅ Check user has ACTIVE access to book in Library
- ✅ Create/Update ReadingProgress
- ✅ Create new ReadingSession

#### 2. Update Progress (While Scrolling)
```http
POST /api/v1/reading/progress
Authorization: Bearer {user_token}
Content-Type: application/json

{
    "book_id": "{book_uuid}",
    "current_page": 45
}
```

**Response:**
```json
{
    "success": true,
    "message": "Reading progress updated",
    "data": {
        "book_id": "uuid",
        "last_page_read": 45,
        "total_pages": 200,
        "progress_percentage": 22.5,
        "last_read_at": "2025-12-18T15:30:00Z"
    }
}
```

**Use Case:**
- Call endpoint setiap user scroll halaman buku
- Progress akan auto-update real-time
- Percentage dihitung otomatis

#### 3. Get Reading Progress
```http
GET /api/v1/reading/progress/{book_id}
Authorization: Bearer {user_token}
```

**Response:**
```json
{
    "success": true,
    "message": "Reading progress fetched",
    "data": {
        "book_id": "uuid",
        "last_page_read": 45,
        "total_pages": 200,
        "progress_percentage": 22.5,
        "last_read_at": "2025-12-18T15:30:00Z",
        "device_info": "Chrome/Windows"
    }
}
```

#### 4. Finish Reading Session
```http
POST /api/v1/reading/finish
Authorization: Bearer {user_token}
Content-Type: application/json

{
    "book_id": "{book_uuid}",
    "last_page_read": 200
}
```

**Response:**
```json
{
    "success": true,
    "message": "Reading saved",
    "data": null
}
```

**Action:**
- Update final progress
- Close active ReadingSession
- Calculate duration_minutes and pages_read

### Review Module

#### 1. Create Review
```http
POST /api/v1/reviews
Authorization: Bearer {user_token}
Content-Type: application/json

{
    "book_id": "{book_uuid}",
    "rating": 5,
    "review_text": "Great book!",
    "is_anonymous": false
}
```

#### 2. Get Book Reviews
```http
GET /api/v1/reviews/{book_id}
```

#### 3. Mark Review Helpful
```http
POST /api/v1/reviews/helpful
Content-Type: application/json

{
    "review_id": "{review_uuid}",
    "is_helpful": true
}
```

## Frontend Integration Example

### JavaScript - Track Scroll Progress
```javascript
// E-reader component
let currentPage = 1;
const totalPages = 200;
const bookId = 'book-uuid-here';

// Update progress setiap user scroll
async function updateReadingProgress(page) {
    try {
        const response = await fetch('/api/v1/reading/progress', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${userToken}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                book_id: bookId,
                current_page: page
            })
        });
        
        const data = await response.json();
        console.log('Progress:', data.data.progress_percentage + '%');
    } catch (error) {
        console.error('Failed to update progress:', error);
    }
}

// Call saat user scroll halaman
document.getElementById('e-reader').addEventListener('scroll', (e) => {
    const newPage = calculateCurrentPage(e.target.scrollTop);
    if (newPage !== currentPage) {
        currentPage = newPage;
        updateReadingProgress(currentPage);
    }
});

// Start session saat buka buku
async function startReading() {
    await fetch('/api/v1/reading/start', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${userToken}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            book_id: bookId,
            device_info: navigator.userAgent
        })
    });
}

// Finish session saat tutup buku
async function finishReading() {
    await fetch('/api/v1/reading/finish', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${userToken}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            book_id: bookId,
            last_page_read: currentPage
        })
    });
}

// Auto-save saat user close tab
window.addEventListener('beforeunload', finishReading);
```

## Complete User Flow

### Step 1: Admin Preparation
```
1. Admin upload PDF/EPUB → POST /api/v1/library/files
2. User beli buku → Payment Module → HMVC grant access
3. Library item created with status ACTIVE
```

### Step 2: User Reading
```
1. User buka "My Library" → GET /api/v1/library
2. User klik "Read" → Create viewer session → POST /api/v1/library/viewer
3. Frontend call START reading → POST /api/v1/reading/start
4. Frontend get stream URL → GET /api/v1/library/stream/{token}
5. User mulai baca, scroll halaman
6. Frontend update progress → POST /api/v1/reading/progress (real-time)
7. User tutup buku → POST /api/v1/reading/finish
```

### Step 3: User Review
```
1. User klik "Write Review"
2. Submit review → POST /api/v1/reviews
3. Other users bisa lihat → GET /api/v1/reviews/{book_id}
```

## Database Schema

### reading_progress
```
id (uuid)
user_id (uuid)
book_id (uuid)
last_page_read (int)
total_pages (int)
progress_percentage (decimal)
last_read_at (datetime)
device_info (string)
```

### reading_sessions
```
id (uuid)
reading_progress_id (uuid, FK)
started_at (datetime)
ended_at (datetime, nullable)
duration_minutes (int, nullable)
pages_read (int, nullable)
```

### reviews
```
id (uuid)
user_id (uuid)
book_id (uuid)
rating (int 1-5)
review_text (text)
is_anonymous (boolean)
created_at, updated_at
```

## Access Control Logic

### Library Module Check
```php
LibraryItem::where('user_id', $userId)
    ->where('book_id', $bookId)
    ->where('status', 'ACTIVE')  // Must be ACTIVE
    ->whereNull('revoked_at')    // Not revoked
    ->exists();
```

### Error Responses

**No Library Access:**
```json
{
    "success": false,
    "message": "You do not have access to read this book.",
    "errors": {
        "book_id": ["You do not have access to read this book."]
    }
}
```

**Book Not Found:**
```json
{
    "success": false,
    "message": "Data not found",
    "errors": null
}
```

## Testing Scenarios

### Scenario 1: Complete Reading Flow
1. Admin uploads book
2. Admin grants access to user
3. User starts reading session
4. User scrolls → progress updates (test multiple times)
5. User finishes reading
6. Check database: reading_progress should show final state

### Scenario 2: Access Control
1. User tries to read book without library access
2. Should get error: "You do not have access to read this book"
3. Admin grants access
4. User can now start reading

### Scenario 3: Real-time Progress
1. Start reading at page 0
2. Update to page 10 → check response (5% progress)
3. Update to page 50 → check response (25% progress)
4. Update to page 200 → check response (100% progress)

### Scenario 4: Review After Reading
1. User reads at least 50% of book
2. User can submit review with rating 1-5
3. Review appears in book's review list

## Performance Considerations

### Progress Update Frequency
- **Don't update every scroll**: Use debouncing
- **Recommended**: Update every 5-10 seconds while reading
- **Or**: Update only when page changes significantly

```javascript
// Debounce example
let updateTimer;
function debouncedProgressUpdate(page) {
    clearTimeout(updateTimer);
    updateTimer = setTimeout(() => {
        updateReadingProgress(page);
    }, 5000); // Wait 5 seconds after last scroll
}
```

## Admin Monitoring

Admin can track user engagement:
```sql
-- Most active readers
SELECT user_id, COUNT(*) as sessions, SUM(duration_minutes) as total_minutes
FROM reading_sessions
GROUP BY user_id
ORDER BY total_minutes DESC;

-- Book popularity
SELECT book_id, COUNT(DISTINCT user_id) as unique_readers
FROM reading_progress
GROUP BY book_id
ORDER BY unique_readers DESC;

-- Average completion rate
SELECT book_id, AVG(progress_percentage) as avg_completion
FROM reading_progress
GROUP BY book_id;
```

## Module Dependencies

```
Review_Reading Module
    ↓ depends on (via interface)
Library Module
    ↓
LibraryAccessServiceImpl (checks user access)
```

**Key Point**: Review_Reading tidak langsung akses Library database, tapi via contract/interface untuk loose coupling.

## Summary

✅ **Integration Complete:**
- Library module provides access control
- Review_Reading module tracks reading progress
- Real-time progress updates while scrolling
- Review system available after reading
- All endpoints tested and documented

✅ **Security:**
- User must have ACTIVE library access
- Token-based file streaming
- Access validation on every request

✅ **User Experience:**
- Seamless reading flow
- Auto-save progress
- Continue from last page
- Review after reading
