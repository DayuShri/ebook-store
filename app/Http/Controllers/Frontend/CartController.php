<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\CartService;
use App\Services\Frontend\WalletFrontendService;
use App\Services\Frontend\LibraryService;
use App\Services\Frontend\VoucherFrontendService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;
    protected WalletFrontendService $walletService;
    protected LibraryService $libraryService;
    protected VoucherFrontendService $voucherService;

    public function __construct(
        CartService $cartService,
        WalletFrontendService $walletService,
        LibraryService $libraryService,
        VoucherFrontendService $voucherService
    ) {
        $this->cartService = $cartService;
        $this->walletService = $walletService;
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

        $walletBalance = $this->walletService->getBalance();
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

        // Check wallet balance
        if (!$this->walletService->hasEnoughBalance($cart['total'])) {
            return redirect()->route('checkout')->with('error', 'Saldo tidak mencukupi. Silakan top-up terlebih dahulu.');
        }

        // Process payment
        $orderId = 'ORD-' . strtoupper(substr(uniqid(), -8));
        $paymentResult = $this->walletService->pay($cart['total'], $orderId, 'Pembelian ' . $cart['item_count'] . ' buku');

        if (!$paymentResult['success']) {
            return redirect()->route('checkout')->with('error', $paymentResult['message']);
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
    }
}
