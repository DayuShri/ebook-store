# Panduan Pengembangan Proyek E-Book Store

## 📋 Daftar Isi

1. [Tentang Proyek](#tentang-proyek)
2. [Struktur Proyek](#struktur-proyek)
3. [Arsitektur Modular](#arsitektur-modular)
4. [Pembagian Tugas](#pembagian-tugas)
5. [Cara Kerja Tim](#cara-kerja-tim)
6. [Standar Kode](#standar-kode)
---

## 🎯 Tentang Proyek

Proyek ini adalah aplikasi **E-Book Store** berbasis Laravel yang menggunakan arsitektur **HMVC (Hierarchical Model-View-Controller)** untuk memisahkan fitur-fitur menjadi modul-modul independen.

### Fitur Utama:

-   🔐 **Authentication & Authorization** - Login, register, role management
-   📚 **Catalog Management** - Manajemen buku, kategori, penulis
-   💰 **Payment & Wallet** - Sistem pembayaran dan dompet digital
-   📖 **Library** - Perpustakaan digital user
-   ⭐ **Review & Rating** - Sistem review dan rating buku
-   🛒 **Order Management** - Manajemen pesanan
-   👤 **User Profile** - Profil dan wishlist user

---

## 📁 Struktur Proyek

```
ebook-store/
├── app/
│   ├── Http/                    # Controllers, Middleware, Requests global
│   ├── Models/                  # Model global (User, dll)
│   └── Modules/                 # 🔥 MODUL-MODUL FITUR
│       ├── Auth/                # Modul Autentikasi
│       │   ├── Controllers/
│       │   ├── Services/
│       │   ├── Requests/
│       │   ├── Routes/
│       │   ├── Models/
│       │   ├── Middleware/
│       │   └── Database/
│       ├── Payment/             # Modul Pembayaran & Wallet
│       │   ├── Controllers/
│       │   ├── Services/
│       │   ├── Models/
│       │   ├── Routes/
│       │   └── Exceptions/
│       ├── Catalog/             # Modul Katalog Buku (BELUM ADA)
│       ├── Order/               # Modul Pesanan (BELUM ADA)
│       ├── Library/             # Modul Perpustakaan (BELUM ADA)
│       └── Review/              # Modul Review (BELUM ADA)
├── database/
│   ├── migrations/              # File migrasi database
│   └── seeders/                 # File seeder
├── routes/
│   ├── api.php                  # Route API utama
│   └── web.php                  # Route web
├── tests/                       # Unit & Feature tests
└── .env                         # Konfigurasi environment
```

---

## 🏗️ Arsitektur Modular

Setiap modul memiliki struktur yang **TERPISAH** dan **INDEPENDEN**:

### Struktur Modul Standar:

```
app/Modules/{NamaModul}/
├── Controllers/          # API Controllers
│   ├── Http/            # Public API (untuk frontend)
│   └── Internal/        # Internal API (antar modul)
├── Services/            # Business Logic Layer
├── Models/              # Eloquent Models
├── Requests/            # Form Request Validation
├── Routes/              # Route definitions
│   ├── api.php         # Public API routes
│   └── hmvc.php        # Internal HMVC routes
├── Middleware/          # Module-specific middleware
├── Database/            # Seeders khusus modul
└── Exceptions/          # Custom exceptions
```

### Perbedaan API dan HMVC:

| Aspek      | Public API (`/api/v1/*`)     | HMVC API (`/hmvc/*`)       |
| ---------- | ---------------------------- | -------------------------- |
| **Tujuan** | Diakses dari frontend/client | Komunikasi antar modul     |
| **Auth**   | ✅ Memerlukan token          | ❌ Tidak perlu auth        |
| **Contoh** | `GET /api/v1/wallet/me`      | `POST /hmvc/wallet/credit` |
| **Lokasi** | `Controllers/Http/`          | `Controllers/Internal/`    |

---

## 👥 Pembagian Tugas

### 🔴 PENTING: Satu Orang = Satu Modul

Untuk menghindari **konflik merge**, setiap developer bertanggung jawab atas **SATU MODUL** saja.

### Contoh Pembagian:

| Developer       | Modul     | Tanggung Jawab                      |
| --------------- | --------- | ----------------------------------- |
| **Developer 1** | `Auth`    | Login, Register, Roles, Permissions |
| **Developer 2** | `Payment` | Wallet, Top-up, Transaksi           |
| **Developer 3** | `Catalog` | Buku, Kategori, Penulis, Publisher  |
| **Developer 4** | `Order`   | Cart, Checkout, Order History       |
| **Developer 5** | `Library` | Perpustakaan user, Download, Read   |
| **Developer 6** | `Review`  | Rating, Review, Komentar            |

### ⚠️ Aturan Penting:

1. **JANGAN** edit file di modul orang lain
2. **JANGAN** edit file global (`routes/api.php`, `config/*`) tanpa koordinasi
3. **SELALU** komunikasikan jika perlu mengubah file shared
4. **GUNAKAN** HMVC API untuk komunikasi antar modul

---

## 🤝 Cara Kerja Tim

### 1. Komunikasi Antar Modul

Jika modul kamu perlu data dari modul lain, **JANGAN** langsung akses model/service mereka. Gunakan **HMVC API**.

#### ❌ SALAH:

```php
// Di modul Order, langsung akses WalletService
use App\Modules\Payment\Services\WalletService;

$walletService = new WalletService();
$walletService->deduct($userId, $amount);
```

#### ✅ BENAR:

```php
// Di modul Order, panggil HMVC API
use Illuminate\Support\Facades\Http;

$response = Http::post(config('app.url') . '/hmvc/wallet/deduct', [
    'user_id' => $userId,
    'amount' => $amount,
    'description' => 'Order #' . $orderId
]);
```

### 2. Membuat Modul Baru

Ikuti langkah berikut:

#### Step 1: Buat Struktur Folder

```bash
mkdir -p app/Modules/NamaModul/{Controllers/Http,Controllers/Internal,Services,Models,Requests,Routes,Middleware}
```

#### Step 2: Buat Service Layer

```php
// app/Modules/NamaModul/Services/NamaModulService.php
<?php

namespace App\Modules\NamaModul\Services;

class NamaModulService
{
    public function doSomething()
    {
        // Business logic di sini
    }
}
```

#### Step 3: Buat Controller

```php
// app/Modules/NamaModul/Controllers/Http/NamaModulController.php
<?php

namespace App\Modules\NamaModul\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\NamaModul\Services\NamaModulService;

class NamaModulController extends Controller
{
    protected $service;

    public function __construct(NamaModulService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->doSomething();
        return response()->json($data);
    }
}
```

#### Step 4: Buat Routes

```php
// app/Modules/NamaModul/Routes/api.php
<?php

use Illuminate\Support\Facades\Route;
use App\Modules\NamaModul\Controllers\Http\NamaModulController;

// JANGAN tambahkan prefix 'v1' karena ModuleServiceProvider sudah otomatis menambahkan 'api/v1'
Route::prefix('namamodul')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [NamaModulController::class, 'index']);
});

// Route ini akan otomatis jadi: GET /api/v1/namamodul
```

#### Step 5: Routes Otomatis Ter-load! ✅

**KABAR BAIK:** Kamu **TIDAK PERLU** mendaftarkan routes secara manual!

Proyek ini sudah menggunakan `ModuleServiceProvider` yang **otomatis** memuat semua routes dari setiap modul.

**Cara kerjanya:**

-   `ModuleServiceProvider` akan scan folder `app/Modules/`
-   Untuk setiap modul, akan otomatis load:
    -   `Routes/api.php` → Auto-prefix `/api/v1`
    -   `Routes/hmvc.php` → **TIDAK** auto-prefix (harus define manual)

**Jadi yang perlu kamu lakukan:**

1. ✅ Buat file `Routes/api.php` di modul kamu
2. ✅ Tulis routes di dalamnya (seperti Step 4)
3. ✅ **SELESAI!** Routes langsung bisa diakses

**Contoh API Routes:**

```php
// app/Modules/Catalog/Routes/api.php
Route::prefix('catalog')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/books', [BookController::class, 'index']);
});

// Otomatis jadi: GET /api/v1/catalog/books
```

**Contoh HMVC Routes:**

```php
// app/Modules/Catalog/Routes/hmvc.php
// PENTING: Harus define prefix 'hmvc/...' sendiri!
Route::prefix('hmvc/catalog')->group(function () {
    Route::post('/update-stock', [StockController::class, 'update']);
});

// Jadi: POST /hmvc/catalog/update-stock
```

> **💡 Tips:** Gunakan `php artisan route:list` untuk melihat semua routes yang ter-load

### 3. Koordinasi File Shared

File-file berikut adalah **SHARED** dan perlu koordinasi:

-   `config/*` - Konfigurasi aplikasi
-   `database/migrations/*` - Migrasi database
-   `.env` - Environment variables
-   `composer.json` - Dependencies
-   `app/Providers/*` - Service providers

> **💡 Catatan:** `routes/api.php` **JARANG** perlu diubah karena routes modul otomatis ter-load via `ModuleServiceProvider`

**Cara koordinasi:**

1. Buat **issue** di GitHub sebelum edit
2. Informasikan di grup chat
3. Merge sesegera mungkin setelah review
4. Pull changes terbaru sebelum mulai kerja

---

## 📝 Standar Kode

### 1. Naming Convention

| Item                | Convention          | Contoh                                |
| ------------------- | ------------------- | ------------------------------------- |
| **Class**           | PascalCase          | `WalletService`, `OrderController`    |
| **Method**          | camelCase           | `getUserWallet()`, `processPayment()` |
| **Variable**        | camelCase           | `$userId`, `$totalAmount`             |
| **Constant**        | UPPER_SNAKE_CASE    | `MAX_UPLOAD_SIZE`                     |
| **Database Table**  | snake_case (plural) | `users`, `order_items`                |
| **Database Column** | snake_case          | `user_id`, `created_at`               |

### 2. Response Format

Gunakan format response yang **KONSISTEN**:

#### Success Response:

```php
return response()->json([
    'success' => true,
    'message' => 'Data berhasil diambil',
    'data' => $data
], 200);
```

#### Error Response:

```php
return response()->json([
    'success' => false,
    'message' => 'Terjadi kesalahan',
    'errors' => $errors
], 400);
```

### 3. Validation

Gunakan **Form Request** untuk validasi:

```php
// app/Modules/NamaModul/Requests/CreateItemRequest.php
<?php

namespace App\Modules\NamaModul\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama harus diisi',
            'price.required' => 'Harga harus diisi',
        ];
    }
}
```

### 4. Service Layer Pattern

**SELALU** pisahkan business logic ke Service Layer:

```php
// ❌ JANGAN di Controller
public function store(Request $request)
{
    $wallet = Wallet::where('user_id', auth()->id())->first();
    $wallet->balance += $request->amount;
    $wallet->save();

    Transaction::create([...]);
    // dst...
}

// ✅ LAKUKAN di Service
public function store(Request $request)
{
    $result = $this->walletService->topUp(
        auth()->id(),
        $request->amount
    );

    return response()->json($result);
}
```

---

## ✅ Summary

### Yang Harus Diingat:

1. **Satu orang = Satu modul** - Jangan edit modul orang lain
2. **Gunakan HMVC API** - Untuk komunikasi antar modul
3. **Service Layer** - Business logic di Service, bukan Controller
4. **Git Workflow** - Branch → Commit → Push → PR → Review → Merge
5. **Komunikasi** - Selalu update progress dan koordinasi
6. **Testing** - Test sebelum push
7. **Code Quality** - Follow naming convention & best practices
