<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\LibraryService;
use App\Services\Frontend\VoucherFrontendService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected LibraryService $libraryService;
    protected VoucherFrontendService $voucherService;

    public function __construct(
        LibraryService $libraryService,
        VoucherFrontendService $voucherService
    ) {
        $this->libraryService = $libraryService;
        $this->voucherService = $voucherService;
    }

    /**
     * Get authentication token for API calls
     */
    protected function getAuthToken(): ?string
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        if ($user->currentAccessToken()) {
            return $user->currentAccessToken()->token;
        }
        
        $sessionToken = session('api_token');
        if ($sessionToken) {
            $tokenExists = \Laravel\Sanctum\PersonalAccessToken::findToken($sessionToken);
            if ($tokenExists && $tokenExists->tokenable_id === $user->id) {
                return $sessionToken;
            }
        }

        try {
            $token = $user->createToken('web-session-' . now()->timestamp)->plainTextToken;
            session(['api_token' => $token]);
            return $token;
        } catch (\Exception $e) {
            Log::error('Failed to create session token', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get cart from backend API and format for views
     */
    protected function getCartFromApi(): array
    {
        try {
            $token = $this->getAuthToken();
            if (!$token) {
                return $this->emptyCart();
            }

            $response = Http::withToken($token)
                ->get(config('app.url') . '/api/v1/cart');

            if ($response->failed()) {
                return $this->emptyCart();
            }

            $cartItems = $response->json('data', []);
            
            // Enrich cart items with book details from Catalog
            $catalogService = app(\App\Modules\Catalog\Services\CatalogService::class);
            $enrichedItems = [];
            $subtotal = 0;

            foreach ($cartItems as $item) {
                try {
                    $book = $catalogService->getBookDetail($item['book_id']);
                    if ($book) {
                        $price = $book['price'];
                        $discountPct = $book['discount_percentage'] ?? 0;
                        $effectivePrice = $price - ($price * $discountPct / 100);
                        $quantity = $item['quantity'] ?? 1;
                        $itemSubtotal = $effectivePrice * $quantity;

                        $enrichedItems[] = [
                            'id' => $item['id'] ?? uniqid('cart-'),
                            'book_id' => $item['book_id'],
                            'quantity' => $quantity,
                            'is_selected' => true, // Backend cart doesn't have selection yet
                            'book' => [
                                'id' => $book['id'],
                                'title' => $book['title'],
                                'price' => $book['price'],
                                'discount_percentage' => $book['discount_percentage'] ?? 0,
                                'cover_image_url' => $book['cover_image_url'],
                                'authors' => isset($book['author']) ? [['name' => $book['author']]] : [],
                                'categories' => $book['categories'] ?? [],
                            ],
                            'subtotal' => $itemSubtotal,
                        ];

                        $subtotal += $itemSubtotal;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to enrich cart item with book details', [
                        'book_id' => $item['book_id'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // TODO: Apply voucher discount if stored in session
            $discount = 0;
            $total = $subtotal - $discount;

            return [
                'items' => $enrichedItems,
                'selected_items' => $enrichedItems, // All items selected by default
                'voucher' => null, // TODO: Get from session if applied
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'item_count' => count($enrichedItems),
                'selected_count' => count($enrichedItems),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch cart from API', [
                'error' => $e->getMessage(),
            ]);
            return $this->emptyCart();
        }
    }

    /**
     * Return empty cart structure
     */
    protected function emptyCart(): array
    {
        return [
            'items' => [],
            'selected_items' => [],
            'voucher' => null,
            'subtotal' => 0,
            'discount' => 0,
            'total' => 0,
            'item_count' => 0,
            'selected_count' => 0,
        ];
    }

    /**
     * Display cart page
     */
    public function index()
    {
        $cart = $this->getCartFromApi();
        $vouchers = $this->voucherService->getActiveVouchers();

        return view('frontend.cart.index', [
            'cart' => $cart,
            'cartCount' => $cart['total'],
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(string $bookId)
    {
        try {
            $token = $this->getAuthToken();
            if (!$token) {
                return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
            }

            // Try API call first
            try {
                $response = Http::timeout(3)->withToken($token)
                    ->post(config('app.url') . '/api/v1/cart/items', [
                        'book_id' => $bookId,
                        'quantity' => 1,
                    ]);

                if ($response->successful()) {
                    if (request()->ajax()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Buku ditambahkan ke keranjang',
                        ]);
                    }
                    return redirect()->back()->with('success', 'Buku ditambahkan ke keranjang');
                }

                $errorMessage = $response->json('message', 'Gagal menambahkan ke keranjang');
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Fallback: Use direct Cart model if HTTP fails
                Log::info('API timeout, using direct Cart model', ['book_id' => $bookId]);
                
                $cart = \App\Modules\Order\Models\Cart::updateOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'book_id' => $bookId,
                    ],
                    [
                        'quantity' => 1,
                        'added_at' => now(),
                    ]
                );

                if (request()->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Buku ditambahkan ke keranjang',
                    ]);
                }
                return redirect()->back()->with('success', 'Buku ditambahkan ke keranjang');
            }
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage ?? 'Gagal menambahkan ke keranjang',
                ]);
            }
            return redirect()->back()->with('error', $errorMessage ?? 'Gagal menambahkan ke keranjang');
        } catch (\Exception $e) {
            Log::error('Exception adding to cart', [
                'book_id' => $bookId,
                'error' => $e->getMessage(),
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan',
                ]);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * Remove item from cart
     */
    public function remove(string $bookId)
    {
        try {
            $token = $this->getAuthToken();
            if (!$token) {
                return redirect()->route('login');
            }

            $response = Http::withToken($token)
                ->delete(config('app.url') . '/api/v1/cart/items/' . $bookId);

            if ($response->successful()) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Buku dihapus dari keranjang',
                    ]);
                }
                return redirect()->route('cart.index')->with('success', 'Buku dihapus dari keranjang');
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus dari keranjang',
                ]);
            }
            return redirect()->route('cart.index')->with('error', 'Gagal menghapus dari keranjang');
        } catch (\Exception $e) {
            Log::error('Exception removing from cart', [
                'book_id' => $bookId,
                'error' => $e->getMessage(),
            ]);

            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan']);
            }
            return redirect()->route('cart.index')->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * Update item quantity
     */
    public function update(Request $request, string $bookId)
    {
        try {
            $token = $this->getAuthToken();
            if (!$token) {
                return redirect()->route('login');
            }

            $quantity = (int) $request->input('quantity', 1);

            $response = Http::withToken($token)
                ->patch(config('app.url') . '/api/v1/cart/items/' . $bookId, [
                    'quantity' => $quantity,
                ]);

            if ($response->successful()) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Jumlah diperbarui',
                    ]);
                }
                return redirect()->route('cart.index')->with('success', 'Jumlah diperbarui');
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui jumlah',
                ]);
            }
            return redirect()->route('cart.index')->with('error', 'Gagal memperbarui jumlah');
        } catch (\Exception $e) {
            Log::error('Exception updating cart', [
                'book_id' => $bookId,
                'error' => $e->getMessage(),
            ]);

            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan']);
            }
            return redirect()->route('cart.index')->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * Display checkout page
     */
    public function checkout()
    {
        $cart = $this->getCartFromApi();

        if ($cart['total'] === 0) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong');
        }

        // Get wallet balance using direct service call
        $walletBalance = 0;
        try {
            $walletService = app(\App\Modules\Payment\Services\WalletService::class);
            $wallet = $walletService->getWallet(auth()->id());
            $walletBalance = $wallet->balance ?? 0;
        } catch (\Exception $e) {
            Log::error('Failed to fetch wallet balance for checkout', [
                'error' => $e->getMessage(),
            ]);
        }

        // Calculate total from cart items
        $total = 0;
        foreach ($cart['items'] as $item) {
            $total += ($item['quantity'] ?? 1) * 10000; // Placeholder price calculation
        }

        $canPay = $walletBalance >= $total;
        $vouchers = $this->voucherService->getActiveVouchers();

        return view('frontend.cart.checkout', [
            'cart' => $cart,
            'walletBalance' => $walletBalance,
            'canPay' => $canPay,
            'cartCount' => $cart['total'],
            'vouchers' => $vouchers,
            'total' => $total,
        ]);
    }

    /**
     * Process payment
     */
    public function pay()
    {
        $cart = $this->getCartFromApi();

        if ($cart['total'] === 0) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong');
        }

        try {
            $token = $this->getAuthToken();
            if (!$token) {
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
            }

            // Get book IDs from cart
            $selectedBookIds = array_map(fn($item) => $item['book_id'], $cart['items']);
            $voucherCode = session('applied_voucher_code'); // Get from session if stored

            // Call Order module's place-order API
            $response = Http::withToken($token)
                ->post(config('app.url') . '/api/v1/checkout/place-order', [
                    'selected_book_ids' => $selectedBookIds,
                    'voucher_code' => $voucherCode,
                    'payment_method' => 'wallet',
                ]);

            if ($response->failed()) {
                $errorMessage = $response->json('message', 'Pembayaran gagal');
                $errors = $response->json('errors', []);
                
                Log::error('Order placement failed', [
                    'selected_books' => $selectedBookIds,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                if (isset($errors['voucher_code'])) {
                    $errorMessage = $errors['voucher_code'][0] ?? $errorMessage;
                }

                return redirect()->route('checkout')->with('error', $errorMessage);
            }

            $responseData = $response->json('data', []);
            $order = $responseData['order'] ?? null;

            // Fallback: Grant library access if not already done by Payment callback
            if ($order && isset($order['items'])) {
                foreach ($order['items'] as $item) {
                    $this->libraryService->addToLibrary($item['book_id'], $order['id']);
                }
            }

            return redirect()->route('library.index')->with('success', 'Pembayaran berhasil! Buku sudah ditambahkan ke perpustakaan Anda.');
        } catch (\Exception $e) {
            Log::error('Exception processing payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('checkout')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
