<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\CartService;
use App\Services\Frontend\WalletFrontendService;
use App\Services\Frontend\LibraryService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;
    protected WalletFrontendService $walletService;
    protected LibraryService $libraryService;

    public function __construct(
        CartService $cartService,
        WalletFrontendService $walletService,
        LibraryService $libraryService
    ) {
        $this->cartService = $cartService;
        $this->walletService = $walletService;
        $this->libraryService = $libraryService;
    }

    /**
     * Display cart page
     */
    public function index()
    {
        $cart = $this->cartService->getCart();

        return view('frontend.cart.index', [
            'cart' => $cart,
            'cartCount' => $cart['item_count'],
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
            return redirect()->route('cart.index')->with('success', $result['message']);
        }

        return redirect()->route('cart.index')->with('error', $result['message']);
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

        return redirect()->route('cart.index')->with('success', $result['message']);
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

        return view('frontend.cart.checkout', [
            'cart' => $cart,
            'walletBalance' => $walletBalance,
            'canPay' => $canPay,
            'cartCount' => $cart['item_count'],
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
        foreach ($cart['items'] as $item) {
            $this->libraryService->addToLibrary($item['book_id'], $orderId);
        }

        // Clear cart
        $this->cartService->clearCart();

        return redirect()->route('library.index')->with('success', 'Pembayaran berhasil! Buku sudah ditambahkan ke perpustakaan Anda.');
    }
}
