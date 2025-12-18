<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\CatalogController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\WalletController;
use App\Http\Controllers\Frontend\LibraryController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ============================================================================
// PUBLIC ROUTES
// ============================================================================

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Catalog
Route::get('/books', [CatalogController::class, 'index'])->name('books.index');
Route::get('/books/{id}', [CatalogController::class, 'show'])->name('books.show');
Route::get('/search', [CatalogController::class, 'search'])->name('search');
Route::get('/category/{slug}', [CatalogController::class, 'category'])->name('category');

// Auth (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Payment success callback (from Xendit)
Route::get('/payment/success', function () {
    return view('frontend.payment.success');
})->name('payment.success');

// ============================================================================
// AUTHENTICATED ROUTES
// ============================================================================

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{bookId}', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/remove/{bookId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::put('/cart/update/{bookId}', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/voucher', [CartController::class, 'applyVoucher'])->name('cart.voucher');
    Route::delete('/cart/voucher', [CartController::class, 'removeVoucher'])->name('cart.voucher.remove');

    // Checkout
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/pay', [CartController::class, 'pay'])->name('checkout.pay');

    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/topup', [WalletController::class, 'topupForm'])->name('wallet.topup');
    Route::post('/wallet/topup', [WalletController::class, 'topup'])->name('wallet.topup.submit');

    // Library
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::get('/library/read/{bookId}', [LibraryController::class, 'read'])->name('library.read');
    Route::post('/library/progress/{bookId}', [LibraryController::class, 'updateProgress'])->name('library.progress');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Wishlist
    Route::get('/wishlist', [ProfileController::class, 'wishlist'])->name('wishlist');
    Route::post('/wishlist/{bookId}', [ProfileController::class, 'addToWishlist'])->name('wishlist.add');
    Route::delete('/wishlist/{bookId}', [ProfileController::class, 'removeFromWishlist'])->name('wishlist.remove');
});

// ============================================================================
// ADMIN ROUTES (placeholder)
// ============================================================================

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        return view('frontend.admin.dashboard');
    })->name('dashboard');
});
