# Frontend Integration Guide - E-Book Store

## 📋 Daftar Isi
1. [Setup Environment](#setup-environment)
2. [Konfigurasi Database](#konfigurasi-database)
3. [Module yang Tersedia](#module-yang-tersedia)
4. [Flow Frontend ke Backend](#flow-frontend-ke-backend)
5. [Testing Frontend](#testing-frontend)

---

## 🔧 Setup Environment

### 1. Copy dan Configure `.env`
```bash
cp .env.example .env
```

### 2. Update `.env` dengan konfigurasi berikut:

```dotenv
# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ebook_store
DB_USERNAME=root
DB_PASSWORD=

# Supabase (untuk file storage)
SUPABASE_URL=your_supabase_url
SUPABASE_KEY=your_supabase_anon_key
SUPABASE_BUCKET=bukuku

# AWS S3 (Supabase menggunakan S3 protocol)
AWS_ACCESS_KEY_ID=your_supabase_access_key
AWS_SECRET_ACCESS_KEY=your_supabase_secret_key
AWS_DEFAULT_REGION=ap-south-1
AWS_BUCKET=bukuku
AWS_ENDPOINT=your_supabase_s3_endpoint
AWS_USE_PATH_STYLE_ENDPOINT=true
FILESYSTEM_DISK=supabase

# Xendit Payment Gateway
XENDIT_SECRET_KEY=your_xendit_secret_key
XENDIT_WEBHOOK_TOKEN=your_webhook_token

# Internal Service URLs (semua dalam 1 aplikasi)
CATALOG_BASE_URL=http://localhost:8000
PAYMENT_BASE_URL=http://localhost:8000
WALLET_BASE_URL=http://localhost:8000
```

### 3. Install Dependencies
```bash
composer install
npm install
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

---

## 💾 Konfigurasi Database

### 1. Buat Database
```sql
CREATE DATABASE ebook_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Seed Data (Optional untuk testing)
```bash
php artisan db:seed --class=CatalogSeeder
php artisan db:seed --class=DemoSeeder
```

---

## 📦 Module yang Tersedia

Setelah merge, aplikasi memiliki module-module berikut:

### 1. **Auth Module** 
- Login/Register
- Profile Management
- User Management (Admin)
- **Routes:** `/api/v1/auth/*`
- **Frontend:** `/login`, `/register`, `/profile`

### 2. **Catalog Module**
- Browse Books
- Search & Filter
- Categories
- Book Details
- **Routes:** `/api/v1/catalog/*`
- **Frontend:** `/books`, `/books/{id}`, `/category/{slug}`

### 3. **Wishlist Module**
- Add/Remove from Wishlist
- View Wishlist
- **Routes:** `/api/v1/wishlist/*`
- **Frontend:** `/wishlist`

### 4. **Cart & Order Module**
- Shopping Cart
- Apply Vouchers
- Checkout Process
- Order Management
- **Routes:** `/api/cart/*`, `/api/checkout/*`, `/api/vouchers/*`
- **Frontend:** `/cart`, `/checkout`

### 5. **Payment & Wallet Module**
- Wallet Balance
- Top-up via Xendit
- Payment Processing
- Transaction History
- **Routes:** `/api/v1/wallet/*`, `/api/v1/payment/*`
- **Frontend:** `/wallet`, `/wallet/topup`

### 6. **Library Module**
- User's Library (Purchased Books)
- E-Book Reader/Viewer
- File Streaming
- **Routes:** `/api/v1/library/*`
- **Frontend:** `/library`, `/library/read/{bookId}`

### 7. **Review & Reading Progress Module**
- Book Reviews
- Reading Progress Tracking
- **Routes:** `/api/v1/reading/*`, `/api/v1/reviews/*`
- **Frontend:** `/library/reviews/{bookId}`

---

## 🔄 Flow Frontend ke Backend

### Architecture Overview

```
┌─────────────────┐
│  Browser/User   │
└────────┬────────┘
         │ HTTP Request
         ▼
┌─────────────────────────────────────────┐
│         Laravel Routes (web.php)         │
│  - Frontend Controllers                  │
└────────┬────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│      Frontend Services Layer            │
│  - CartService                           │
│  - CatalogService                        │
│  - WalletFrontendService                 │
│  - LibraryService                        │
│  - UserFrontendService                   │
│  - ReviewService                         │
└────────┬────────────────────────────────┘
         │ HTTP Client (Guzzle)
         ▼
┌─────────────────────────────────────────┐
│      Backend API (api.php)               │
│  - Module Routes (/api/v1/*)            │
│  - Order/Voucher Routes (/api/cart/*)   │
└────────┬────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│      Backend Services & Models           │
│  - Database Operations                   │
│  - Business Logic                        │
│  - External API Calls (Xendit)          │
└─────────────────────────────────────────┘
```

### Example: Browse Books Flow

1. **User** → visits `/books`
2. **Web Route** → `CatalogController@index`
3. **Frontend Service** → `CatalogService->getBooks()`
4. **HTTP Request** → `GET /api/v1/catalog/books`
5. **Backend API** → `BookController@index`
6. **Backend Service** → `BookService->getBooks()`
7. **Database** → Query books table
8. **Response** → JSON data back through the chain
9. **Blade View** → Render data in `catalog/index.blade.php`

### Example: Checkout Flow

1. User clicks "Checkout" button
2. `CartController@checkout` loads cart & wallet balance
3. User confirms payment
4. `CartController@pay` sends request to:
   - `/api/checkout/place-order` (creates order)
   - `/api/v1/payment/deduct` (deducts from wallet)
   - `/api/v1/library` (grants book access)
5. Redirect to Library with success message

---

## 🧪 Testing Frontend

### 1. Start Development Server

```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Vite Dev Server (if using Vite)
npm run dev

# Terminal 3: Queue Worker (for async jobs)
php artisan queue:work
```

### 2. Test Workflow

#### A. **Registration & Login**
1. Visit `http://localhost:8000/register`
2. Create account
3. Login at `http://localhost:8000/login`

#### B. **Browse & Add to Cart**
1. Visit `http://localhost:8000/books`
2. Browse books, use filters
3. Click book to view details
4. Click "Add to Cart"
5. View cart at `http://localhost:8000/cart`

#### C. **Top-up Wallet**
1. Visit `http://localhost:8000/wallet`
2. Click "Top Up"
3. Enter amount (min 10,000)
4. Select payment method
5. Submit (will use simulation in test mode)

#### D. **Checkout & Purchase**
1. Go to cart `http://localhost:8000/cart`
2. Optional: Apply voucher code
3. Click "Checkout"
4. Confirm order at `/checkout`
5. Click "Pay Now"
6. Wallet will be deducted
7. Book added to library

#### E. **Read Book in Library**
1. Visit `http://localhost:8000/library`
2. Click "Read" on any book
3. Book viewer opens with streaming
4. Progress auto-saved
5. Can add reviews

#### F. **Wishlist**
1. Browse books `/books`
2. Click heart icon to add to wishlist
3. View all wishlist at `/wishlist`
4. Can move items to cart

---

## 🚀 Running in Production

### 1. Environment Setup
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

### 2. Optimize Application
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 3. Build Frontend Assets
```bash
npm run build
```

### 4. Setup Queue Worker (for background jobs)
```bash
# Using Supervisor or systemd
php artisan queue:work --daemon
```

### 5. Setup Cron (for scheduled tasks)
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📝 Important Notes

### API Authentication

Frontend menggunakan **session-based authentication** Laravel, sementara API menggunakan **Sanctum tokens**.

- Web routes (`/books`, `/cart`, dll) → Session Auth
- API routes (`/api/v1/*`) → Sanctum Token Auth
- Frontend Services handle token management otomatis

### CORS Configuration

Jika frontend dan backend di server berbeda, update `config/cors.php`:

```php
'paths' => ['api/*', 'hmvc/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['http://localhost:3000'], // Your frontend URL
'supports_credentials' => true,
```

### File Upload

E-books disimpan di Supabase Storage. Pastikan:
1. Bucket `bukuku` sudah dibuat di Supabase
2. Access policy set to authenticated
3. File format: PDF, EPUB (support bisa ditambah)

### Payment Integration

**Development Mode:**
- Xendit in test mode
- Wallet top-up akan simulasi (langsung berhasil)

**Production Mode:**
- Set `XENDIT_SECRET_KEY` dengan production key
- Setup webhook URL di Xendit dashboard
- Handle real payment callbacks di `/api/payment/callback`

---

## 🐛 Troubleshooting

### Issue: "SQLSTATE[HY000] [2002] Connection refused"
**Fix:** Pastikan MySQL server running
```bash
# Linux/Mac
sudo systemctl start mysql

# Windows with XAMPP
Start MySQL from XAMPP Control Panel
```

### Issue: "Class 'GuzzleHttp\Client' not found"
**Fix:** Install dependencies
```bash
composer install
```

### Issue: "Storage path not writable"
**Fix:** Set permissions
```bash
chmod -R 775 storage bootstrap/cache
```

### Issue: Books tidak muncul di Library setelah purchase
**Fix:** 
1. Check queue worker running: `php artisan queue:work`
2. Check `jobs` table untuk failed jobs
3. Check logs: `storage/logs/laravel.log`

### Issue: Xendit payment stuck
**Fix:**
1. Verify Xendit credentials in `.env`
2. Check webhook URL configured correctly
3. Test with Xendit test mode first
4. Check `storage/logs` for error details

---

## 📚 Additional Resources

- **API Documentation:** See `postman/` folder untuk collection
- **Module Documentation:** See `INSTRUCTION.md`
- **Library Integration:** See `LIBRARY-FLOW-DOCUMENTATION.md`
- **Laravel Docs:** https://laravel.com/docs
- **Xendit Docs:** https://developers.xendit.co

---

## ✅ Checklist Setup

- [ ] `.env` configured dengan semua keys
- [ ] Database created dan migrations run
- [ ] Composer dependencies installed
- [ ] NPM dependencies installed
- [ ] Application key generated
- [ ] Supabase bucket configured
- [ ] Test account created
- [ ] Sample books seeded
- [ ] Queue worker running
- [ ] Development server running
- [ ] Test checkout flow berhasil
- [ ] Test library access berhasil

Jika semua checklist terpenuhi, frontend sudah siap digunakan! 🎉
