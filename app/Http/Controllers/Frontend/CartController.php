<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\CartService;
use App\Services\Frontend\LibraryService;
use App\Services\Frontend\VoucherFrontendService;
use App\Services\Frontend\WalletFrontendService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected CartService $cartService;
    protected LibraryService $libraryService;
    protected VoucherFrontendService $voucherService;

    public function __construct(
        CartService $cartService,
        LibraryService $libraryService,
        VoucherFrontendService $voucherService
    ) {
        $this->cartService = $cartService;
        $this->libraryService = $libraryService;
        $this->voucherService = $voucherService;
    }

    /**
     * Display cart page
     */
    public function index()
    {
        $cart = $this->cartService->getCart();

        $vouchers = $this->voucherService->getActiveVouchers();

        return view('frontend.cart.index', [
            'cart' => $cart,
            'cartCount' => $cart['item_count'],
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(string $bookId)
    {
        $result = $this->cartService->addItem($bookId);

        if (request()->ajax()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Remove item from cart
     */
    public function remove(string $bookId)
    {
        $result = $this->cartService->removeItem($bookId);

        if (request()->ajax()) {
            return response()->json($result);
        }

        return redirect()->route('cart.index')->with('success', $result['message']);
    }

    /**
     * Update item quantity
     */
    public function update(Request $request, string $bookId)
    {
        $quantity = (int) $request->input('quantity', 1);
        $result = $this->cartService->updateQuantity($bookId, $quantity);

        if (request()->ajax()) {
            return response()->json($result);
        }

        return redirect()->route('cart.index')->with('success', $result['message']);
    }

    /**
     * Update item selection
     */
    public function updateSelection(Request $request, string $bookId)
    {
        $isSelected = $request->boolean('is_selected');
        $result = $this->cartService->updateSelection($bookId, $isSelected);

        if (request()->ajax()) {
            // Get updated totals
            $cart = $this->cartService->getCart();
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => [
                    'subtotal' => number_format($cart['subtotal'], 0, ',', '.'),
                    'discount' => number_format($cart['discount'], 0, ',', '.'),
                    'total' => number_format($cart['total'], 0, ',', '.'),
                    'selected_count' => $cart['selected_count'],
                ]
            ]);
        }

        return redirect()->back();
    }

    /**
     * Apply voucher code
     */
    public function applyVoucher(Request $request)
    {
        $code = $request->input('code', '');
        $result = $this->cartService->applyVoucher($code);

        if (request()->ajax()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Display voucher selection page
     */
    public function selectVoucher()
    {
        $cart = $this->cartService->getCart();
        $vouchers = $this->voucherService->getActiveVouchers();

        return view('frontend.cart.select-voucher', [
            'cart' => $cart,
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * Remove voucher
     */
    public function removeVoucher()
    {
        $result = $this->cartService->removeVoucher();

        if (request()->ajax()) {
            return response()->json($result);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Display checkout page
     */
    public function checkout()
    {
        $cart = $this->cartService->getCart();

        if ($cart['item_count'] === 0) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong');
        }

        // Get wallet balance from Payment module's Public API
        $walletBalance = 0;
        try {
            $response = Http::withToken(auth()->user()->currentAccessToken()->token)
                ->get(config('app.url') . '/api/v1/wallet/me');

            if ($response->successful()) {
                $data = $response->json('data');
                $walletBalance = $data['balance'] ?? 0;
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch wallet balance for checkout', [
                'error' => $e->getMessage(),
            ]);
        }

        $canPay = $walletBalance >= $cart['total'];
        $vouchers = $this->voucherService->getActiveVouchers();

        // Filter items to show only selected ones for checkout
        $cart['items'] = $cart['selected_items'];

        if (count($cart['items']) === 0) {
            return redirect()->route('cart.index')->with('error', 'Pilih minimal satu buku untuk checkout');
        }

        return view('frontend.cart.checkout', [
            'cart' => $cart,
            'walletBalance' => $walletBalance,
            'canPay' => $canPay,
            'cartCount' => $cart['selected_count'],
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * Process payment
     */
    public function pay()
    {
        $cart = $this->cartService->getCart();

        if ($cart['item_count'] === 0) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong');
        }

        // Generate order ID
        $orderId = 'ORD-' . strtoupper(substr(uniqid(), -8));

        try {
            // Call Payment module's Public API to deduct balance
            $response = Http::withToken(auth()->user()->currentAccessToken()->token)
                ->post(config('app.url') . '/api/v1/payment/deduct', [
                    'order_id' => $orderId,
                    'user_id' => auth()->id(),
                    'amount' => $cart['total'],
                    'payment_method' => 'wallet',
                ]);

            if ($response->failed()) {
                $errorMessage = $response->json('message', 'Pembayaran gagal');
                
                Log::error('Payment deduction failed', [
                    'order_id' => $orderId,
                    'amount' => $cart['total'],
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return redirect()->route('checkout')->with('error', $errorMessage);
            }

        // Add books to library
        // Add books to library
        // Only add selected items!
        // We know $cart contains all items, so we filter by selected_items (which getCart returns)
        foreach ($cart['selected_items'] as $item) {
            $this->libraryService->addToLibrary($item['book_id'], $orderId);
        }

        // Remove only selected items from cart
        $this->cartService->removeSelectedItems();

            return redirect()->route('library.index')->with('success', 'Pembayaran berhasil! Buku sudah ditambahkan ke perpustakaan Anda.');
        } catch (\Exception $e) {
            Log::error('Exception processing payment', [
                'order_id' => $orderId,
                'amount' => $cart['total'],
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('checkout')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
