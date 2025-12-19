# Quick Start - E-Book Reader Frontend Testing

## Prerequisites
```bash
php artisan serve
# Server running at: http://localhost:8000
```

## Step-by-Step Testing

### 1. Login User (Setup)
```bash
# Atau via Postman/API
POST /api/v1/auth/login
{
    "email": "user@example.com",
    "password": "password"
}

# Save token untuk testing
```

### 2. Admin Upload Book (jika belum ada)
```bash
POST /api/v1/library/files
Authorization: Bearer {admin_token}

book_id: {book_uuid}
file: your_book.pdf
```

### 3. Admin Grant Access ke User
```bash
POST /api/v1/library
Authorization: Bearer {admin_token}

{
    "user_id": "{user_uuid}",
    "book_id": "{book_uuid}"
}
```

### 4. Open Library Page
```
Browser → http://localhost:8000/library
```

**Expected:**
- List of books muncul
- Progress percentage ditampilkan
- Button "Start Reading" atau "Continue Reading"

### 5. Click "Start Reading"
**Automatic Actions:**
1. ✅ Start reading session → POST /api/v1/reading/start
2. ✅ Create viewer session → POST /api/v1/library/viewer
3. ✅ Fetch PDF → GET /api/v1/library/stream/{token}
4. ✅ Load progress → GET /api/v1/reading/progress/{book_id}
5. ✅ Render PDF page 1 (or last read page)

**Expected:**
- PDF viewer loads
- Page counter: "Page 1 of 200" (example)
- Progress bar: 0% atau last saved progress
- Navigation buttons enabled

### 6. Navigate Pages
**Test:**
- Click "Next" → Page 2
- Click "Previous" → Page 1
- Type "50" in input, press Enter → Page 50
- Press Arrow Right key → Page 51
- Press Arrow Left key → Page 50

**Expected:**
- Page renders correctly
- Page counter updates
- Progress bar updates
- Progress percentage updates

### 7. Wait 3 Seconds
**Expected:**
- Console log: "Progress saved: Page 50"
- Network tab shows: POST /api/v1/reading/progress

### 8. Test Zoom
- Click "+" button → Zoom to 125%
- Click "+" again → Zoom to 150%
- Click "-" button → Zoom to 125%

### 9. Close & Reopen
1. **Close browser tab**
   - Expected: Final progress saved automatically
   
2. **Reopen: http://localhost:8000/library**
   - Expected: Progress shows updated percentage
   
3. **Click "Continue Reading"**
   - Expected: Resume from page 50 (last saved)

### 10. Check Database
```sql
-- Check reading progress
SELECT * FROM reading_progress 
WHERE user_id = '{user_uuid}' 
AND book_id = '{book_uuid}';

-- Should show:
-- last_page_read: 50
-- progress_percentage: 25.00 (if 200 pages total)
-- last_read_at: recent timestamp

-- Check reading session
SELECT * FROM reading_sessions 
WHERE reading_progress_id = '{progress_uuid}'
ORDER BY started_at DESC 
LIMIT 1;

-- Should show:
-- ended_at: NOT NULL
-- duration_minutes: calculated
-- pages_read: calculated
```

## Test Scenarios

### Scenario 1: New Book (First Time)
```
1. User never read this book before
2. Open reader
3. Expected: Start from page 1, progress 0%
4. Navigate to page 10
5. Close and reopen
6. Expected: Resume from page 10
```

### Scenario 2: Continue Reading
```
1. User already read 50% (page 100 of 200)
2. Open reader
3. Expected: Resume from page 100, progress 50%
4. Read to page 150
5. Progress should show 75%
```

### Scenario 3: Multiple Devices
```
1. User reads on Desktop → Page 50
2. Progress saved to server
3. User opens on Mobile (same account)
4. Expected: Resume from page 50
```

### Scenario 4: No Access
```
1. User tries to read book without library access
2. Expected: Redirect with error message
```

## Visual Checklist

### Library Page
- [ ] Books list displayed
- [ ] Progress percentage shown
- [ ] "Start Reading" button visible
- [ ] "Continue Reading" for books with progress

### Reader Page
- [ ] PDF loads correctly
- [ ] Header shows book title
- [ ] Page counter displays correctly
- [ ] Progress bar visible and updates
- [ ] Navigation buttons work
- [ ] Zoom controls functional
- [ ] Keyboard shortcuts work

### Progress Tracking
- [ ] Progress saves automatically (every 3 seconds)
- [ ] Console logs "Progress saved: Page X"
- [ ] Database updated with correct values
- [ ] Resume from last page works
- [ ] Progress percentage calculated correctly

### Session Management
- [ ] Session starts on page load
- [ ] Session finishes on page unload
- [ ] Duration calculated correctly
- [ ] Pages read tracked

## Common Issues & Solutions

### PDF not loading
**Check:**
1. Console errors?
2. Stream URL accessible? → Test in Postman
3. Viewer token valid?
4. Supabase file exists?

**Solution:**
```javascript
// Open browser console, check for errors
// Look for failed network requests
```

### Progress not saving
**Check:**
1. Auth token valid?
2. Reading session started?
3. Network tab shows POST requests?

**Debug:**
```javascript
// In browser console
console.log('AUTH_TOKEN:', AUTH_TOKEN);
console.log('sessionStarted:', sessionStarted);
```

### Page counter wrong
**Check:**
1. PDF loaded completely?
2. `totalPages` value correct?

**Debug:**
```javascript
// In browser console
console.log('pdfDoc:', pdfDoc);
console.log('totalPages:', totalPages);
```

## Frontend Features Checklist

### Core Features
- [x] PDF viewer dengan PDF.js
- [x] Auto-detect total pages
- [x] Track current page
- [x] Progress bar real-time
- [x] Auto-save progress (debounced)
- [x] Resume from last page
- [x] Session management

### Navigation
- [x] Next/Previous buttons
- [x] Go to page input
- [x] Keyboard shortcuts (arrow keys)

### UI/UX
- [x] Clean modern design
- [x] Progress percentage display
- [x] Page counter
- [x] Zoom in/out
- [x] Loading indicator
- [x] Error messages

### Performance
- [x] Lazy loading pages
- [x] Debounced API calls
- [x] Canvas rendering
- [x] BeforeUnload save

## API Call Sequence

When user opens reader:

```
1. GET /reader/{bookId} (Web route)
   → ReaderController checks access
   → Returns viewer.blade.php

2. POST /api/v1/reading/start
   → Create/update reading_progress
   → Create reading_session

3. POST /api/v1/library/viewer
   → Create viewer_session
   → Returns token & stream_url

4. GET /api/v1/library/stream/{token}
   → Redirects to Supabase signed URL
   → Browser downloads PDF

5. GET /api/v1/reading/progress/{book_id}
   → Get last saved progress
   → Resume from last page

6. [Every 3 seconds] POST /api/v1/reading/progress
   → Update current page
   → Calculate percentage

7. [On close] POST /api/v1/reading/finish
   → End reading_session
   → Calculate duration & pages_read
```

## Success Criteria

✅ **User can:**
1. See their library of books
2. Open any book with access
3. Read PDF smoothly
4. Navigate between pages
5. Zoom in/out
6. Have progress auto-saved
7. Resume from last read page
8. See real-time progress updates

✅ **System tracks:**
1. Current page
2. Total pages (from PDF)
3. Progress percentage
4. Reading duration
5. Pages read per session
6. Device info
7. Last read timestamp

✅ **Security:**
1. Only authenticated users
2. Only books user owns
3. Token-based file access
4. Session expiration (2 hours)

## Next Steps

1. **Test all scenarios** above
2. **Check database** for correct tracking
3. **Try different PDFs** (various page counts)
4. **Test on mobile** (responsive design)
5. **Monitor performance** (check console timing)

## Quick Command Reference

```bash
# Start server
php artisan serve

# Check routes
php artisan route:list --path=reader
php artisan route:list --path=library

# Check logs
tail -f storage/logs/laravel.log

# Database queries
php artisan tinker
>>> DB::table('reading_progress')->latest()->first();
>>> DB::table('reading_sessions')->latest()->first();
```

---

**Ready to test!** 🚀

Open browser → http://localhost:8000/library
