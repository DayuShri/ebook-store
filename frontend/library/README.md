# Library & Reading Frontend Components

Folder ini berisi semua file frontend untuk modul **Library** dan **Review_Reading**.

## 📁 File Structure

```
frontend/library/
├── README.md                 # Dokumentasi ini
├── login.blade.php          # Halaman login dengan token authentication
├── dashboard.blade.php      # Dashboard dengan statistik membaca
├── library-index.blade.php  # Daftar buku di library user
├── reader.blade.php         # PDF reader dengan progress tracking
└── reviews.blade.php        # Daftar review yang dibuat user
```

## 🔗 API Endpoints yang Digunakan

### Authentication
- `POST /api/v1/auth/login` - Login dan generate token

### Library Module
- `GET /api/v1/library` - Get user's book collection
- `POST /api/v1/library/viewer` - Create viewer session (validate grant)
- `GET /api/v1/library/stream/{token}` - Stream PDF from Supabase

### Reading Module
- `POST /api/v1/reading/start` - Start reading session
- `POST /api/v1/reading/progress` - Save reading progress
- `GET /api/v1/reading/progress/{book_id}` - Get reading progress

### Review Module
- `POST /api/v1/reading/review` - Submit book review
- `GET /api/v1/reading/reviews` - Get user's all reviews
- `DELETE /api/v1/reading/review/{id}` - Delete review

## 🚀 Reading Flow (ALUR LENGKAP)

```
1. USER HAS GRANT
   library_item: status=ACTIVE, revoked_at=NULL
   
2. USER KLIK BUKU
   Redirect ke /reader/{bookId}
   
3. CREATE VIEWER SESSION ✓
   POST /api/v1/library/viewer
   - Validasi grant (library_item)
   - Generate token untuk streaming
   - Return stream_url
   
4. START READING SESSION
   POST /api/v1/reading/start
   - Record mulai baca
   - Device info, IP
   
5. LOAD PDF FROM SUPABASE
   GET /api/v1/library/stream/{token}
   - Redirect ke signed URL
   - Auto-detect total pages
   - Resume dari last page
   
6. READING & PROGRESS TRACKING
   - User scroll/navigasi
   - Auto-save setiap 3 detik
   POST /api/v1/reading/progress
   
7. SELESAI BACA (Optional)
   - Review modal muncul
   POST /api/v1/reading/review
```

## 🎨 Features

### Login (login.blade.php)
- ✅ Email & password authentication
- ✅ Token persistence di localStorage
- ✅ Token validation sebelum redirect

### Dashboard (dashboard.blade.php)
- ✅ 4 Stat cards: Total, In progress, Completed, Overall
- ✅ Continue reading section
- ✅ Recently completed section

### Library (library-index.blade.php)
- ✅ Grid display dengan progress bars
- ✅ Format badge (PDF/EPUB)
- ✅ Clickable cards → reader

### Reader (reader.blade.php)
- ✅ PDF.js v3.11.174
- ✅ Progress bar real-time
- ✅ Auto-save setiap 3 detik
- ✅ Review modal saat selesai

### Reviews (reviews.blade.php)
- ✅ Grid display reviews
- ✅ Star rating
- ✅ Edit & Delete

## 📝 Notes untuk Integration

1. Copy files ke `resources/views/`
2. Update `routes/web.php`
3. Pastikan CORS enabled
4. Sanctum config published

---

**Created**: December 19, 2025  
**Module**: Library & Review_Reading
