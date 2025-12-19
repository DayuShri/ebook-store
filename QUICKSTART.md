# Quick Start Guide - E-Book Store

## 🚀 Jalankan Aplikasi (Development)

### Prerequisites
✅ PHP 8.2+
✅ MySQL/MariaDB
✅ Composer
✅ Node.js & NPM

---

## Option 1: Setup Otomatis (Recommended)

```bash
# Jalankan setup script
php setup.php
```

Script akan otomatis:
- Install dependencies (Composer & NPM)
- Generate app key
- Setup database (opsional)
- Seed demo data (opsional)

---

## Option 2: Setup Manual

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database
Edit `.env`:
```dotenv
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=ebook_store
DB_USERNAME=root
DB_PASSWORD=
```

Buat database:
```sql
CREATE DATABASE ebook_store;
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Seed Data (Optional)
```bash
php artisan db:seed --class=CatalogSeeder
php artisan db:seed --class=DemoSeeder
```

---

## 🎯 Jalankan Server

### Terminal 1: Laravel Server
```bash
php artisan serve
```
Server akan berjalan di: `http://localhost:8000`

### Terminal 2: Queue Worker (PENTING!)
```bash
php artisan queue:work
```
Diperlukan untuk:
- Proses checkout
- Grant akses library
- Background jobs

### Terminal 3: Vite Dev Server (Optional)
```bash
npm run dev
```
Untuk hot-reload CSS/JS saat development

---

## 📱 Akses Aplikasi

### Frontend (User)
- **Home:** http://localhost:8000
- **Books:** http://localhost:8000/books
- **Login:** http://localhost:8000/login
- **Register:** http://localhost:8000/register

### After Login:
- **Cart:** http://localhost:8000/cart
- **Wallet:** http://localhost:8000/wallet
- **Library:** http://localhost:8000/library
- **Profile:** http://localhost:8000/profile
- **Wishlist:** http://localhost:8000/wishlist

### Admin
- **Dashboard:** http://localhost:8000/admin

### API Endpoints
- **Health Check:** http://localhost:8000/api/health
- **API Docs:** Lihat folder `postman/` untuk collections

---

## 🧪 Test User Accounts

Setelah seed, gunakan:

**Regular User:**
- Email: `user@example.com`
- Password: `password`

**Admin:**
- Email: `admin@example.com`
- Password: `password`

---

## 🔧 Konfigurasi Tambahan

### Supabase (File Storage)
Edit `.env`:
```dotenv
SUPABASE_URL=your_supabase_url
SUPABASE_KEY=your_supabase_key
SUPABASE_BUCKET=bukuku

AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_ENDPOINT=your_s3_endpoint
AWS_USE_PATH_STYLE_ENDPOINT=true
```

### Xendit (Payment Gateway)
Edit `.env`:
```dotenv
XENDIT_SECRET_KEY=your_xendit_key
XENDIT_WEBHOOK_TOKEN=your_webhook_token
```

**Development Mode:** Top-up wallet akan otomatis sukses (simulasi)
**Production Mode:** Perlu setup webhook di Xendit dashboard

---

## 📊 Database Structure

### Main Tables
- `users` - User accounts
- `user_profiles` - User profile details
- `books` - Book catalog
- `book_categories` - Categories
- `wishlists` - User wishlists
- `carts` - Shopping carts
- `orders` - Order history
- `order_items` - Order details
- `vouchers` - Discount vouchers
- `wallets` - User wallet balances
- `wallet_transactions` - Transaction history
- `library_accesses` - User's purchased books
- `reading_progress` - Reading progress tracking
- `reviews` - Book reviews

---

## 🛠️ Useful Commands

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Check Routes
```bash
php artisan route:list
```

### Check Queue Jobs
```bash
php artisan queue:failed       # Failed jobs
php artisan queue:retry all    # Retry failed jobs
```

### Run Tests
```bash
php artisan test
```

### Fresh Migration (⚠️ Menghapus semua data!)
```bash
php artisan migrate:fresh --seed
```

---

## 🐛 Common Issues

### "Connection refused" Error
- Pastikan MySQL server running
- Check DB credentials di `.env`

### Books tidak muncul setelah purchase
- Pastikan queue worker running: `php artisan queue:work`
- Check failed jobs: `php artisan queue:failed`

### "Class not found" Error
```bash
composer dump-autoload
php artisan optimize:clear
```

### Storage permission error
```bash
chmod -R 775 storage bootstrap/cache
```

---

## 📚 Documentation

- **Frontend Integration:** `FRONTEND-INTEGRATION-GUIDE.md`
- **General Instructions:** `INSTRUCTION.md`
- **Library Flow:** `LIBRARY-FLOW-DOCUMENTATION.md`
- **API Testing:** Lihat folder `postman/`

---

## 🎯 Complete Workflow Test

1. **Register** new account
2. **Browse** books at `/books`
3. **Add** book to cart
4. **Top-up** wallet at `/wallet/topup`
5. **Checkout** and purchase
6. **View** purchased book in `/library`
7. **Read** book (e-book viewer)
8. **Add** review after reading

---

## 💡 Tips

- Gunakan **3 terminal** untuk development (server, queue, vite)
- Check `storage/logs/laravel.log` untuk debugging
- Gunakan **Postman collections** di folder `postman/` untuk API testing
- Module routes auto-loaded dari `app/Modules/*/Routes/`

---

**Need Help?** Check documentation atau contact team! 🚀
