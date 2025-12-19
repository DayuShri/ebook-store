# Dokumentasi Alur Library & Reading System

## Overview
Dokumen ini menjelaskan alur lengkap dari admin menambahkan buku hingga user membaca dan memberikan review, menggunakan modul **Library** dan **Review_Reading**.

---

## 🔄 ALUR LENGKAP

### 1️⃣ **Admin Menambahkan Buku**

**Database Tables:**
- `books` - Data buku (title, description, price, dll)
- `book_files` - File buku di Supabase (file_path, file_format, file_size_mb)

**Proses:**
```sql
-- Admin insert buku baru
INSERT INTO books (id, title, description, price, cover_image_url, ...)
VALUES ('book-uuid', 'Laravel Mastery', '...', 299000, 'cover.jpg', ...);

-- Admin upload file PDF/EPUB ke Supabase, kemudian insert record
INSERT INTO book_files (id, book_id, file_path, file_format, file_size_mb)
VALUES ('file-uuid', 'book-uuid', 'books/laravel-mastery.pdf', 'pdf', 12.5);
```

**File Location:** File disimpan di Supabase Storage di bucket yang dikonfigurasi di `.env`
```
SUPABASE_URL=https://xxx.supabase.co
SUPABASE_KEY=eyJxxx...
SUPABASE_BUCKET=ebook-files
```

---

### 2️⃣ **User Memesan Buku**

**Database Tables:**
- `orders` - Order utama
- `order_items` - Item dalam order

**Flow:**
```
User Add to Cart → Cart → Checkout → Create Order (status: pending)
```

**Code Example:**
```php
// User checkout dari cart
$order = Order::create([
    'id' => Str::uuid(),
    'order_number' => 'ORD-'.time(),
    'user_id' => $userId,
    'total_amount' => 299000,
    'status' => 'pending' // PENTING: masih pending
]);

$orderItem = OrderItem::create([
    'id' => Str::uuid(),
    'order_id' => $order->id,
    'book_id' => $book->id,
    'quantity' => 1,
    'unit_price' => $book->price,
    'subtotal' => $book->price
]);
```

---

### 3️⃣ **User Melakukan Pembayaran**

**Payment Module Integration:**

Ketika user membayar melalui **Payment Module** (Wallet/Gateway):

```php
// Payment berhasil, update order status
$order->update(['status' => 'paid']);

// 🚨 TRIGGER GRANT ACCESS 🚨
// Ini harus dilakukan di Payment Module atau Observer
foreach ($order->orderItems as $item) {
    $libraryService->grant(
        $order->user_id,
        $item->book_id,
        $order->id
    );
}
```

**Service Call:**
```php
use App\Modules\Library\Services\LibraryService;

$libraryService = app(LibraryService::class);
$libraryItem = $libraryService->grant($userId, $bookId, $orderId);
```

**Result di Database:**
```sql
-- Tabel: library_items
INSERT INTO library_items (id, user_id, book_id, order_id, status, granted_at)
VALUES ('lib-uuid', 'user-uuid', 'book-uuid', 'order-uuid', 'ACTIVE', NOW());
```

---

### 4️⃣ **User Membuka Library**

**Controller:** `App\Http\Controllers\Frontend\LibraryWebController`

**Route:** `GET /library`

**Implementation:**
```php
public function index()
{
    $userId = auth()->id();
    
    // 1. Ambil library items user
    $libraryService = app(\App\Modules\Library\Services\LibraryService::class);
    $libraryItems = $libraryService->listUserLibrary($userId);
    
    // 2. Ambil reading progress untuk setiap buku
    $readingService = app(\App\Modules\Review_Reading\Services\ReadingService::class);
    
    $result = [];
    foreach ($libraryItems as $item) {
        $progress = DB::table('reading_progress')
            ->where('user_id', $userId)
            ->where('book_id', $item->book_id)
            ->first();
            
        $book = DB::table('books')->where('id', $item->book_id)->first();
        
        $result[] = [
            'library_item' => $item,
            'book' => $book,
            'progress' => $progress,
            'progress_percentage' => $progress ? $progress->progress_percentage : 0
        ];
    }
    
    return view('frontend.library.index', ['libraryItems' => $result]);
}
```

**View Display:**
- ✅ Book cover
- ✅ Progress bar (0-100%)
- ✅ "Baca Sekarang" / "Lanjut Baca" button
- ✅ "Lihat Review" button

---

### 5️⃣ **User Klik "Baca Sekarang"**

**Route:** `GET /library/read/{bookId}`

**Controller Method:**
```php
public function read(string $bookId)
{
    $userId = auth()->id();
    
    // 1. Verify user has access
    $libraryItem = LibraryItem::where('user_id', $userId)
        ->where('book_id', $bookId)
        ->where('status', 'ACTIVE')
        ->firstOrFail();
    
    // 2. Create ViewerSession (token untuk streaming)
    $viewerService = app(\App\Modules\Library\Services\ViewerService::class);
    $session = $viewerService->createSession(
        $userId,
        $bookId,
        null, // auto-detect format
        request()->userAgent(),
        request()->ip(),
        120 // 2 jam
    );
    
    // 3. Start ReadingSession (tracking)
    $readingService = app(\App\Modules\Review_Reading\Services\ReadingService::class);
    $readingService->start($userId, $bookId, request()->userAgent());
    
    // 4. Get stream URL
    $streamUrl = route('library.stream', ['token' => $session->token]);
    
    // 5. Render reader view
    $bookFile = BookFile::where('book_id', $bookId)->first();
    
    return view('frontend.library.reader', [
        'bookId' => $bookId,
        'bookFile' => $bookFile,
        'streamUrl' => $streamUrl,
        'format' => $bookFile->file_format // pdf or epub
    ]);
}
```

**What Happens:**
1. ✅ **ViewerSession** dibuat dengan token unik (expires 2 jam)
2. ✅ **ReadingSession** dimulai untuk tracking durasi
3. ✅ Token digunakan untuk generate Supabase signed URL
4. ✅ File di-stream dari Supabase ke browser

---

### 6️⃣ **Streaming File dari Supabase**

**API Route:** `GET /api/v1/library/stream/{token}`

**Controller:** `App\Modules\Library\Controllers\Api\LibraryStreamController`

**Implementation:**
```php
public function stream(string $token)
{
    $viewerService = app(\App\Modules\Library\Services\ViewerService::class);
    $supabaseService = app(\App\Modules\Library\Services\SupabaseService::class);
    
    // 1. Validate token
    $data = $viewerService->validateTokenAndGetFile($token);
    if (!$data) {
        abort(403, 'Invalid or expired token');
    }
    
    // 2. Get signed URL dari Supabase
    $signedUrl = $supabaseService->getSignedUrl(
        $data['file_path'],
        3600 // 1 hour
    );
    
    // 3. Redirect ke Supabase signed URL
    return redirect($signedUrl);
}
```

**Supabase Service:**
```php
class SupabaseService
{
    protected $client;
    
    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => config('services.supabase.url'),
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.supabase.key')
            ]
        ]);
    }
    
    public function getSignedUrl(string $path, int $expiresIn = 3600): string
    {
        $bucket = config('services.supabase.bucket');
        
        $response = $this->client->post("/storage/v1/object/sign/{$bucket}/{$path}", [
            'json' => ['expiresIn' => $expiresIn]
        ]);
        
        $data = json_decode($response->getBody(), true);
        
        return config('services.supabase.url') . $data['signedURL'];
    }
}
```

---

### 7️⃣ **User Membaca Buku (PDF/EPUB)**

**Reader View:** `resources/views/frontend/library/reader.blade.php`

**Conditional Rendering:**
```blade
@if($format === 'pdf')
    {{-- PDF.js Viewer --}}
    <div id="pdf-viewer">
        <canvas id="pdf-canvas"></canvas>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        const streamUrl = '{{ $streamUrl }}';
        
        pdfjsLib.getDocument(streamUrl).promise.then(pdf => {
            // Render PDF pages
            pdf.getPage(1).then(page => {
                const canvas = document.getElementById('pdf-canvas');
                const context = canvas.getContext('2d');
                const viewport = page.getViewport({ scale: 1.5 });
                
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                
                page.render({
                    canvasContext: context,
                    viewport: viewport
                });
            });
        });
        
        // Update progress setiap 30 detik
        setInterval(() => {
            updateProgress(currentPage, totalPages);
        }, 30000);
    </script>
    
@elseif($format === 'epub')
    {{-- ePub.js Viewer --}}
    <div id="epub-viewer"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/epubjs/dist/epub.min.js"></script>
    <script>
        const book = ePub('{{ $streamUrl }}');
        const rendition = book.renderTo('epub-viewer', {
            width: '100%',
            height: '100vh'
        });
        
        rendition.display();
        
        // Track progress
        rendition.on('relocated', (location) => {
            const progress = book.locations.percentageFromCfi(location.start.cfi);
            updateProgress(Math.floor(progress * totalPages), totalPages);
        });
    </script>
@endif
```

---

### 8️⃣ **Update Reading Progress**

**AJAX Call (Frontend):**
```javascript
function updateProgress(currentPage, totalPages) {
    fetch(`/library/progress/{{ $bookId }}`, {
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
```

**Backend (Controller):**
```php
public function updateProgress(Request $request, string $bookId)
{
    $userId = auth()->id();
    
    $request->validate([
        'current_page' => 'required|integer|min:0',
        'total_pages' => 'required|integer|min:1'
    ]);
    
    $readingService = app(\App\Modules\Review_Reading\Services\ReadingService::class);
    
    // Update progress dan tutup session jika selesai
    $readingService->finish(
        $userId,
        $bookId,
        $request->current_page
    );
    
    return response()->json(['success' => true]);
}
```

**Service Logic:**
```php
public function finish(string $userId, string $bookId, int $lastPage): void
{
    // 1. Cek akses
    $this->ensureUserCanRead($userId, $bookId);
    
    // 2. Update reading_progress
    $progress = ReadingProgress::where([
        'user_id' => $userId,
        'book_id' => $bookId
    ])->firstOrFail();
    
    $percentage = round(($lastPage / $progress->total_pages) * 100, 2);
    
    $progress->update([
        'last_page_read' => $lastPage,
        'progress_percentage' => $percentage,
        'last_read_at' => now()
    ]);
    
    // 3. Tutup reading_session
    $session = ReadingSession::where('reading_progress_id', $progress->id)
        ->whereNull('ended_at')
        ->latest('started_at')
        ->first();
    
    if ($session) {
        $session->update([
            'ended_at' => now(),
            'duration_minutes' => now()->diffInMinutes($session->started_at),
            'pages_read' => $lastPage - $progress->last_page_read
        ]);
    }
}
```

**Database Updates:**
```sql
-- reading_progress
UPDATE reading_progress 
SET last_page_read = 150,
    progress_percentage = 75.00,
    last_read_at = NOW()
WHERE user_id = 'user-uuid' AND book_id = 'book-uuid';

-- reading_sessions
UPDATE reading_sessions
SET ended_at = NOW(),
    duration_minutes = 45,
    pages_read = 50
WHERE id = 'session-uuid';
```

---

### 9️⃣ **User Memberikan Review**

**Route:** `GET /library/reviews/{bookId}`

**View:** Form untuk rating (1-5 bintang) dan review text

**Submit Review:**
```php
public function storeReview(Request $request, string $bookId)
{
    $userId = auth()->id();
    
    $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'review_text' => 'nullable|string|max:1000'
    ]);
    
    $reviewService = app(\App\Modules\Review_Reading\Services\ReviewService::class);
    
    $reviewService->store($userId, [
        'book_id' => $bookId,
        'rating' => $request->rating,
        'review_text' => $request->review_text
    ]);
    
    return redirect()->route('library.reviews', $bookId)
        ->with('success', 'Review berhasil disimpan!');
}
```

**Service Implementation:**
```php
public function store(string $userId, array $data): void
{
    // 1. Cek user punya buku
    if (!$this->library->userHasBook($userId, $data['book_id'])) {
        abort(403, 'You do not own this book');
    }
    
    // 2. Insert/Update review
    Review::updateOrCreate(
        [
            'user_id' => $userId,
            'book_id' => $data['book_id']
        ],
        [
            'id' => Str::uuid(),
            'rating' => $data['rating'],
            'review_text' => $data['review_text'] ?? null,
            'is_verified_purchase' => true
        ]
    );
}
```

---

## 📊 DATABASE SCHEMA SUMMARY

### Library Module Tables

**library_items** - User yang punya akses buku
```
id (UUID, PK)
user_id (UUID, FK → users)
book_id (UUID, FK → books)
order_id (UUID, FK → orders, nullable)
status (ACTIVE | REVOKED)
granted_at (timestamp)
revoked_at (timestamp, nullable)
```

**book_files** - File buku di Supabase
```
id (UUID, PK)
book_id (UUID, FK → books)
file_path (text) - e.g., "books/laravel-mastery.pdf"
file_format (text) - pdf, epub, mobi
file_size_mb (decimal)
encryption_key (text, nullable)
checksum (text, nullable)
created_at, updated_at
```

**viewer_sessions** - Token untuk streaming
```
id (UUID, PK)
user_id (UUID, FK → users)
book_id (UUID, FK → books)
file_id (UUID, FK → book_files)
file_format (string)
token (UUID, unique)
expires_at (timestamp)
device_info (string, nullable)
ip_address (string, nullable)
created_at
```

### Review_Reading Module Tables

**reading_progress** - Progress baca user per buku
```
id (UUID, PK)
user_id (UUID, FK → users)
book_id (UUID, FK → books)
last_page_read (int)
total_pages (int)
progress_percentage (decimal 5,2)
last_read_at (timestamp)
device_info (string, nullable)
UNIQUE(user_id, book_id)
```

**reading_sessions** - Sesi baca individu
```
id (UUID, PK)
reading_progress_id (UUID, FK → reading_progress)
started_at (timestamp)
ended_at (timestamp, nullable)
duration_minutes (int, nullable)
pages_read (int)
```

**reviews** - Review buku dari user
```
id (UUID, PK)
user_id (UUID, FK → users)
book_id (UUID, FK → books)
rating (int 1-5)
review_text (text, nullable)
is_verified_purchase (boolean)
helpful_count (int)
created_at, updated_at
UNIQUE(user_id, book_id)
```

**review_helpfulness** - User vote review helpful atau tidak
```
id (UUID, PK)
review_id (UUID, FK → reviews)
user_id (UUID, FK → users)
is_helpful (boolean)
created_at
UNIQUE(review_id, user_id)
```

---

## 🔑 KEY SERVICES

### 1. LibraryService
```php
namespace App\Modules\Library\Services;

// Fungsi utama:
grant(userId, bookId, orderId): LibraryItem
listUserLibrary(userId): Collection<LibraryItem>
revoke(id): LibraryItem
revokeByUserAndBook(userId, bookId): LibraryItem
```

### 2. ViewerService
```php
namespace App\Modules\Library\Services;

// Fungsi utama:
createSession(userId, bookId, format?, deviceInfo?, ip?, minutes): ViewerSession
validateTokenAndGetFile(token): Array
```

### 3. SupabaseService
```php
namespace App\Modules\Library\Services;

// Fungsi utama:
getSignedUrl(path, expiresIn): string
uploadFile(path, file): Array
deleteFile(path): bool
```

### 4. ReadingService
```php
namespace App\Modules\Review_Reading\Services;

// Fungsi utama:
start(userId, bookId, deviceInfo?): void
finish(userId, bookId, lastPage): void
getProgress(userId, bookId): ReadingProgress
```

### 5. ReviewService
```php
namespace App\Modules\Review_Reading\Services;

// Fungsi utama:
store(userId, data): void
getByBook(bookId): Collection<Review>
helpful(userId, reviewId, isHelpful): void
update(reviewId, userId, data): void
delete(reviewId, userId): void
```

---

## 🚀 INTEGRATION CHECKLIST

### ✅ Already Implemented
- [x] Library module structure
- [x] Review_Reading module structure
- [x] LibraryService.grant()
- [x] ViewerService.createSession()
- [x] ReadingService start/finish
- [x] ReviewService store/get
- [x] Database migrations
- [x] Basic routes

### ⚠️ Needs Implementation
- [ ] Payment → Grant trigger (Order Observer)
- [ ] Unified book reader (PDF + EPUB)
- [ ] Complete LibraryWebController
- [ ] Reading progress UI dengan progress bar
- [ ] Review submission form
- [ ] BookFileSeeder execution
- [ ] Supabase file upload

---

## 📝 NEXT STEPS

1. **Create Order Observer** untuk auto-grant ketika payment success
2. **Seed book_files** table dengan data file yang sudah ada di Supabase
3. **Update LibraryWebController** dengan semua service integration
4. **Create unified reader view** yang bisa handle PDF dan EPUB
5. **Test complete flow** dari order → grant → read → progress → review

---

**Last Updated:** 2025-12-19
**Author:** GitHub Copilot
**Version:** 1.0
