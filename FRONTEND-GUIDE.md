# E-Book Reader Frontend - Complete Guide

## Cara Kerja Sistem

### 1. **Alur Baca Buku (Complete Flow)**

```
User Login
    ↓
My Library (/library)
    ↓
Klik "Start Reading" atau "Continue Reading"
    ↓
Reader Page (/reader/{bookId})
    ↓
System Actions:
    1. Cek akses user (library_items)
    2. Start reading session → /api/v1/reading/start
    3. Create viewer session → /api/v1/library/viewer (dapat token)
    4. Fetch PDF dari Supabase → /api/v1/library/stream/{token}
    5. Load progress tersimpan → /api/v1/reading/progress/{bookId}
    6. Render PDF dengan PDF.js
    ↓
User baca & scroll
    ↓
Auto-save progress setiap 3 detik → /api/v1/reading/progress
    ↓
User tutup/keluar
    ↓
Finish session → /api/v1/reading/finish
```

### 2. **Bagaimana Sistem Tahu Halaman Saat Ini?**

#### Di Frontend (PDF.js):
```javascript
// PDF.js otomatis detect jumlah halaman saat load PDF
pdfDoc = await loadingTask.promise;
totalPages = pdfDoc.numPages;  // 👈 Dapat total pages dari PDF metadata

// User navigasi dengan tombol Next/Previous atau input langsung
currentPage = 1;  // Tracking state di JavaScript

// Setiap render halaman baru
await renderPage(currentPage);
```

#### Tracking Progress:
```javascript
// Saat user ganti halaman (next/previous/goto)
function renderPage(pageNum) {
    // 1. Render halaman via PDF.js
    await page.render(...);
    
    // 2. Update currentPage state
    currentPage = pageNum;
    
    // 3. Hitung percentage
    const percentage = (currentPage / totalPages) * 100;
    
    // 4. Update UI (progress bar, page counter)
    updateProgress();
    
    // 5. Schedule auto-save ke server (debounced 3 detik)
    scheduleProgressUpdate();
}
```

### 3. **Bagaimana Tahu Total Halaman dari Supabase?**

#### PDF Metadata:
```javascript
// PDF.js extract metadata saat load file
const pdfData = await response.arrayBuffer();
const loadingTask = pdfjsLib.getDocument({ data: pdfData });
pdfDoc = await loadingTask.promise;

// PDF sudah contain metadata tentang jumlah halaman
totalPages = pdfDoc.numPages;  // 👈 Baca dari PDF structure

console.log(`Total pages: ${totalPages}`);
// Output: Total pages: 200
```

**Technical Detail:**
- PDF file format punya internal structure (PDF specification)
- PDF.js library bisa parse structure ini
- Metadata include: number of pages, page sizes, fonts, etc.
- Tidak perlu query database, langsung dari file!

#### EPUB (Alternative):
```javascript
// Untuk EPUB, bisa pakai EPUB.js
const book = ePub(url);
await book.ready;
totalPages = book.locations.total;
```

## File Structure

```
resources/views/
├── reader/
│   └── viewer.blade.php       # PDF Reader dengan PDF.js
└── library/
    └── index.blade.php        # Library list page

routes/
└── web.php                     # Web routes untuk reader

app/Modules/Library/Controllers/Http/
└── ReaderController.php       # Controller untuk serve reader page
```

## Pages & Routes

### 1. Library Page
**URL:** `/library`  
**Purpose:** Show user's book collection  
**Features:**
- List semua buku user (dari library_items)
- Show progress percentage masing-masing buku
- Button "Start Reading" atau "Continue Reading"

### 2. Reader Page
**URL:** `/reader/{bookId}`  
**Purpose:** PDF/EPUB reader with progress tracking  
**Features:**
- PDF viewer dengan PDF.js
- Navigation: Previous, Next, Go to Page
- Zoom in/out
- Progress bar real-time
- Auto-save progress
- Resume from last read page
- Keyboard shortcuts (Arrow keys)

## Frontend Components

### PDF.js Integration

**Library:** `pdf.js v3.11.174`  
**CDN:** 
```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
```

**Key Functions:**

#### 1. Load PDF
```javascript
// Fetch dari Supabase via stream URL
const streamUrl = `/api/v1/library/stream/${viewerToken}`;
const response = await fetch(streamUrl);
const pdfData = await response.arrayBuffer();

// Parse dengan PDF.js
const loadingTask = pdfjsLib.getDocument({ data: pdfData });
pdfDoc = await loadingTask.promise;
totalPages = pdfDoc.numPages;
```

#### 2. Render Page
```javascript
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
}
```

#### 3. Navigation
```javascript
function nextPage() {
    if (currentPage < totalPages) {
        renderPage(currentPage + 1);
    }
}

function previousPage() {
    if (currentPage > 1) {
        renderPage(currentPage - 1);
    }
}

function goToPage(pageNum) {
    if (pageNum >= 1 && pageNum <= totalPages) {
        renderPage(pageNum);
    }
}
```

### Progress Tracking

#### Auto-save dengan Debouncing
```javascript
let progressUpdateTimer = null;

function scheduleProgressUpdate() {
    // Cancel previous timer
    clearTimeout(progressUpdateTimer);
    
    // Set new timer (save after 3 seconds of inactivity)
    progressUpdateTimer = setTimeout(async () => {
        await saveProgressToServer();
    }, 3000);
}

async function saveProgressToServer() {
    const response = await fetch('/api/v1/reading/progress', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${AUTH_TOKEN}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            book_id: BOOK_ID,
            current_page: currentPage
        })
    });
}
```

**Why Debouncing?**
- User mungkin scroll cepat through multiple pages
- Tidak perlu save setiap detik (server overhead)
- Save setelah 3 detik idle = lebih efficient

#### Resume dari Last Page
```javascript
async function loadProgress() {
    const response = await fetch(`/api/v1/reading/progress/${BOOK_ID}`, {
        headers: {
            'Authorization': `Bearer ${AUTH_TOKEN}`
        }
    });

    if (response.ok) {
        const data = await response.json();
        currentPage = data.data.last_page_read || 1;
        console.log(`Resuming from page ${currentPage}`);
    }
}
```

## UI Features

### Progress Bar
```html
<div class="progress-bar">
    <div class="progress-fill" id="progress-fill"></div>
</div>
<span id="progress-percent">0%</span>
```

```javascript
function updateProgress() {
    const percentage = Math.round((currentPage / totalPages) * 100);
    document.getElementById('progress-fill').style.width = percentage + '%';
    document.getElementById('progress-percent').textContent = percentage + '%';
}
```

### Page Counter
```html
Page <span id="current-page">1</span> of <span id="total-pages">200</span>
```

### Zoom Controls
```javascript
let scale = 1.5;  // Default zoom

function zoomIn() {
    scale += 0.25;
    if (scale > 3) scale = 3;  // Max 300%
    renderPage(currentPage);
}

function zoomOut() {
    scale -= 0.25;
    if (scale < 0.5) scale = 0.5;  // Min 50%
    renderPage(currentPage);
}
```

### Keyboard Shortcuts
```javascript
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') previousPage();
    if (e.key === 'ArrowRight') nextPage();
    if (e.key === '+') zoomIn();
    if (e.key === '-') zoomOut();
});
```

## Session Management

### Start Session (on page load)
```javascript
async function startReadingSession() {
    await fetch('/api/v1/reading/start', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${AUTH_TOKEN}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            book_id: BOOK_ID,
            device_info: navigator.userAgent
        })
    });
    
    sessionStarted = true;
}
```

### Finish Session (on page unload)
```javascript
window.addEventListener('beforeunload', async (e) => {
    // Save final progress
    await saveProgressToServer();
    
    // Close session (use sendBeacon for reliability)
    navigator.sendBeacon('/api/v1/reading/finish', JSON.stringify({
        book_id: BOOK_ID,
        last_page_read: currentPage
    }));
});
```

**Why `sendBeacon`?**
- Normal `fetch` bisa cancelled saat page unload
- `sendBeacon` guaranteed to send before page close
- Best for analytics & session tracking

## Security & Authentication

### Token Generation
```php
// ReaderController.php
return view('reader.viewer', [
    'bookId' => $bookId,
    'authToken' => $user->createToken('reader-access')->plainTextToken
]);
```

### Token Usage
```javascript
// Di Blade template
const AUTH_TOKEN = '{{ $authToken }}';

// Semua API call gunakan token ini
fetch(url, {
    headers: {
        'Authorization': `Bearer ${AUTH_TOKEN}`
    }
});
```

### Access Control
```php
// Check user access sebelum serve reader
$libraryItem = DB::table('library_items')
    ->where('user_id', $user->id)
    ->where('book_id', $bookId)
    ->where('status', 'ACTIVE')
    ->whereNull('revoked_at')
    ->first();

if (!$libraryItem) {
    return redirect()->back()
        ->with('error', 'You do not have access to this book');
}
```

## Testing Frontend

### 1. Akses Library Page
```
URL: http://localhost:8000/library
Login required: Yes

Expected:
- Show list of user's books
- Each book show progress percentage
- Button "Start Reading" or "Continue Reading"
```

### 2. Open Reader
```
Click "Start Reading" on any book
→ Redirect to /reader/{bookId}

Expected:
- PDF loads and renders
- Show page 1 (or last saved page)
- Progress bar shows 0% (or saved percentage)
- Navigation buttons work
```

### 3. Test Navigation
```
1. Click "Next" → Page 2 renders
2. Click "Previous" → Page 1 renders
3. Type "10" in page input, press Enter → Page 10 renders
4. Use arrow keys → Navigate pages
```

### 4. Test Progress Saving
```
1. Navigate to page 50
2. Wait 3 seconds
3. Check console: "Progress saved: Page 50"
4. Refresh page
5. Expected: Resume from page 50
```

### 5. Test Zoom
```
1. Click "+" button → Zoom in
2. Click "-" button → Zoom out
3. Verify: Zoom level displays (e.g., "125%")
```

## Performance Optimization

### 1. Lazy Loading
- Only load current page, not entire PDF at once
- PDF.js handles this automatically

### 2. Debounced Progress Save
- Don't save on every page change
- Wait 3 seconds after last navigation
- Reduce server requests

### 3. Canvas Rendering
- Use HTML5 Canvas for PDF rendering
- Hardware accelerated
- Smooth scrolling

### 4. BeforeUnload Save
- Use `sendBeacon` for guaranteed final save
- Non-blocking operation

## Browser Compatibility

**Supported:**
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

**Required Features:**
- HTML5 Canvas
- ES6 JavaScript
- Fetch API
- BeforeUnload event

## Troubleshooting

### Issue: PDF tidak load
**Solution:**
1. Check console untuk error
2. Verify stream URL accessible: `/api/v1/library/stream/{token}`
3. Check viewer session created successfully
4. Verify CORS settings untuk Supabase

### Issue: Progress tidak save
**Solution:**
1. Check network tab untuk API calls
2. Verify auth token valid
3. Check reading session started
4. Console log should show "Progress saved: Page X"

### Issue: Halaman tidak render
**Solution:**
1. Check PDF.js worker loaded
2. Verify canvas element exists
3. Check browser console untuk errors
4. Try different zoom level

## Future Enhancements

### Possible Improvements:
1. **Annotations** - Highlight & notes
2. **Bookmarks** - Save favorite pages
3. **Search** - Full-text search dalam PDF
4. **Dark Mode** - Eye-friendly reading
5. **Reading Speed** - Track pages/minute
6. **Mobile Responsive** - Touch gestures
7. **Offline Mode** - Service Worker cache
8. **Multiple Formats** - EPUB, MOBI support

## API Endpoints Used

```
POST   /api/v1/reading/start          # Start session
POST   /api/v1/reading/progress       # Update progress
GET    /api/v1/reading/progress/{id}  # Get progress
POST   /api/v1/reading/finish         # Finish session
POST   /api/v1/library/viewer         # Create viewer session
GET    /api/v1/library/stream/{token} # Stream PDF file
GET    /api/v1/library                # Get user library
```

## Summary

✅ **Complete E-Reader Implementation:**
- PDF viewer dengan PDF.js
- Auto-detect total pages dari PDF metadata
- Track current page via JavaScript state
- Real-time progress tracking & saving
- Resume from last read page
- Clean UI dengan progress bar
- Keyboard navigation
- Zoom in/out functionality
- Session management
- Secure token-based access

✅ **How It Works:**
1. User buka reader → Load PDF dari Supabase
2. PDF.js parse file → Get total pages automatically
3. User scroll/navigate → Track current page in JS
4. Auto-save progress → Debounced 3 seconds
5. User close → Finish session with final page

✅ **User Experience:**
- One-click reading from library
- Automatic progress tracking
- Continue from last page
- Smooth navigation
- Real-time feedback
