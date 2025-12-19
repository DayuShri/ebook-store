# Integration Mapping

Panduan untuk mengintegrasikan frontend files ke Laravel views.

## File Mapping

| Frontend File | Laravel View Path | Route |
|--------------|------------------|-------|
| `login.blade.php` | `resources/views/auth/login.blade.php` | `GET /login` |
| `dashboard.blade.php` | `resources/views/dashboard/index.blade.php` | `GET /dashboard` |
| `library-index.blade.php` | `resources/views/library/index.blade.php` | `GET /library` |
| `reviews.blade.php` | `resources/views/library/reviews.blade.php` | `GET /reviews` |
| `reader.blade.php` | `resources/views/reader/viewer.blade.php` | `GET /reader/{bookId}` |

## Required Routes (routes/web.php)

```php
Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
});

Route::get('/library', function () {
    return view('library.index');
});

Route::get('/reviews', function () {
    return view('library.reviews');
});

Route::get('/reader/{bookId}', [ReaderController::class, 'show'])
    ->name('reader.show');
```

## API Routes Already Configured

✅ All API routes in:
- `app/Modules/Library/Routes/api.php`
- `app/Modules/Review_Reading/Routes/api.php`

## Dependencies

✅ PDF.js CDN (already included in reader.blade.php)
✅ Laravel Sanctum (already configured)
✅ CORS Middleware (already added to bootstrap/app.php)

## Quick Integration Steps

1. Copy files:
```bash
cp frontend/library/login.blade.php resources/views/auth/
cp frontend/library/dashboard.blade.php resources/views/dashboard/
cp frontend/library/library-index.blade.php resources/views/library/index.blade.php
cp frontend/library/reviews.blade.php resources/views/library/
cp frontend/library/reader.blade.php resources/views/reader/viewer.blade.php
```

2. Routes are already configured in `routes/web.php`

3. Test:
- Visit `http://localhost:8000/login`
- Login with credentials
- Navigate through dashboard → library → reader

## Notes

- All files use **vanilla JavaScript** (no framework dependencies)
- Token stored in **localStorage**
- API base URL: `http://localhost:8000/api/v1`
- All API calls include `Authorization: Bearer {token}` header
