<?php

namespace App\Services\Frontend;

use App\Services\Frontend\CatalogService;
use App\Services\Frontend\VoucherFrontendService;

/**
 * Cart Service - Mock Data Layer
 * 
 * This service provides mock cart functionality.
 * Uses session storage for cart state.
 */
class CartService
{
    private const SESSION_KEY = 'shopping_cart';
    private CatalogService $catalogService;
    private VoucherFrontendService $voucherService;

    public function __construct(CatalogService $catalogService, VoucherFrontendService $voucherService)
    {
        $this->catalogService = $catalogService;
        $this->voucherService = $voucherService;
    }

    /**
     * Get cart contents
     */
    public function getCart(): array
    {
        $cart = session(self::SESSION_KEY, [
            'items' => [],
            'voucher' => null,
        ]);

        // Enrich items with book data
        $enrichedItems = [];
        foreach ($cart['items'] as $item) {
            $book = $this->catalogService->getBook($item['book_id']);
            if ($book) {
                $enrichedItems[] = [
                    'id' => $item['id'],
                    'book_id' => $item['book_id'],
                    'book' => $book,
                    'quantity' => $item['quantity'],
                    'is_selected' => $item['is_selected'] ?? true,
                    'subtotal' => $this->calculateItemPrice($book) * $item['quantity'],
                ];
            }
        }

        // Calculate totals only for selected items
        $selectedItems = array_filter($enrichedItems, fn($item) => $item['is_selected']);
        $subtotal = array_sum(array_column($selectedItems, 'subtotal'));
        $discount = $this->calculateVoucherDiscount($cart['voucher'], $subtotal);

        return [
            'items' => $enrichedItems,
            'selected_items' => array_values($selectedItems),
            'voucher' => $cart['voucher'],
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount,
            'item_count' => array_sum(array_column($enrichedItems, 'quantity')),
            'selected_count' => count($selectedItems),
        ];
    }

    /**
     * Add item to cart
     */
    public function addItem(string $bookId): array
    {
        $cart = session(self::SESSION_KEY, ['items' => [], 'voucher' => null]);

        // Check if book exists
        $book = $this->catalogService->getBook($bookId);
        if (!$book) {
            return ['success' => false, 'message' => 'Buku tidak ditemukan'];
        }

        // Check if already in cart (for ebooks, quantity is always 1)
        foreach ($cart['items'] as $item) {
            if ($item['book_id'] === $bookId) {
                return ['success' => false, 'message' => 'Buku sudah ada di keranjang'];
            }
        }

        // Add to cart
        $cart['items'][] = [
            'id' => uniqid('cart-'),
            'book_id' => $bookId,
            'quantity' => 1, // Ebooks are always quantity 1
            'is_selected' => true,
        ];

        session([self::SESSION_KEY => $cart]);

        return ['success' => true, 'message' => 'Buku ditambahkan ke keranjang'];
    }

    /**
     * Remove item from cart
     */
    public function removeItem(string $bookId): array
    {
        $cart = session(self::SESSION_KEY, ['items' => [], 'voucher' => null]);

        $cart['items'] = array_values(array_filter($cart['items'], function ($item) use ($bookId) {
            return $item['book_id'] !== $bookId;
        }));

        session([self::SESSION_KEY => $cart]);

        return ['success' => true, 'message' => 'Buku dihapus dari keranjang'];
    }

    /**
     * Update item quantity (for ebooks, this is limited)
     */
    public function updateQuantity(string $bookId, int $quantity): array
    {
        if ($quantity < 1) {
            return $this->removeItem($bookId);
        }

        // For ebooks, quantity is always 1
        return ['success' => true, 'message' => 'Jumlah diperbarui'];
    }

    /**
     * Update item selection state
     */
    public function updateSelection(string $bookId, bool $isSelected): array
    {
        $cart = session(self::SESSION_KEY, ['items' => [], 'voucher' => null]);

        $found = false;
        foreach ($cart['items'] as &$item) {
            if ($item['book_id'] === $bookId) {
                $item['is_selected'] = $isSelected;
                $found = true;
                break;
            }
        }
        unset($item); // Break the reference to avoid corruption in next loop

        if (!$found) {
            return ['success' => false, 'message' => 'Buku tidak ditemukan di keranjang'];
        }

        // Re-calculate totals to check voucher validity
        $enrichedItems = [];
        foreach ($cart['items'] as $cartItem) { // Use distinct variable name
            $book = $this->catalogService->getBook($cartItem['book_id']);
            if ($book) {
                $enrichedItems[] = [
                    'is_selected' => $cartItem['is_selected'] ?? true,
                    'subtotal' => $this->calculateItemPrice($book) * $cartItem['quantity'],
                ];
            }
        }

        $selectedItems = array_filter($enrichedItems, fn($item) => $item['is_selected']);
        $subtotal = array_sum(array_column($selectedItems, 'subtotal'));

        $message = 'Pilihan diperbarui';

        // Check if voucher is still valid
        if ($cart['voucher']) {
            if ($subtotal < $cart['voucher']['min_purchase_amount']) {
                // Remove voucher if minimum purchase not met
                $cart['voucher'] = null;
                $message = 'Pilihan diperbarui. Voucher dihapus karena minimum belanja tidak terpenuhi.';
            }
        }

        session([self::SESSION_KEY => $cart]);

        return ['success' => true, 'message' => $message];
    }

    /**
     * Remove selected items (after checkout)
     */
    public function removeSelectedItems(): void
    {
        $cart = session(self::SESSION_KEY, ['items' => [], 'voucher' => null]);

        $cart['items'] = array_values(array_filter($cart['items'], function ($item) {
            return !($item['is_selected'] ?? true);
        }));

        // Reset voucher after checkout if cart becomes empty or logic dictates
        if (empty($cart['items'])) {
            $cart['voucher'] = null;
        }

        session([self::SESSION_KEY => $cart]);
    }

    /**
     * Apply voucher code
     */
    public function applyVoucher(string $code): array
    {
        $voucher = $this->validateVoucher($code);

        if (!$voucher) {
            return ['success' => false, 'message' => 'Kode voucher tidak valid atau sudah kadaluarsa'];
        }

        $cart = session(self::SESSION_KEY, ['items' => [], 'voucher' => null]);
        $cart['voucher'] = $voucher;
        session([self::SESSION_KEY => $cart]);

        return [
            'success' => true,
            'message' => 'Voucher berhasil diterapkan',
            'voucher' => $voucher
        ];
    }

    /**
     * Remove voucher
     */
    public function removeVoucher(): array
    {
        $cart = session(self::SESSION_KEY, ['items' => [], 'voucher' => null]);
        $cart['voucher'] = null;
        session([self::SESSION_KEY => $cart]);

        return ['success' => true, 'message' => 'Voucher dihapus'];
    }

    /**
     * Clear entire cart
     */
    public function clearCart(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Get cart item count
     */
    public function getItemCount(): int
    {
        $cart = session(self::SESSION_KEY, ['items' => []]);
        return count($cart['items']);
    }

    /**
     * Calculate price considering discount
     */
    private function calculateItemPrice(array $book): float
    {
        $price = $book['price'];
        if (!empty($book['discount_percentage']) && $book['discount_percentage'] > 0) {
            $price = $price * (1 - $book['discount_percentage'] / 100);
        }
        return $price;
    }

    /**
     * Validate voucher code
     */
    private function validateVoucher(string $code): ?array
    {
        $code = strtoupper(trim($code));
        $voucher = $this->voucherService->getActiveVouchers()
            ->firstWhere('code', $code);

        if ($voucher) {
            return $voucher->toArray();
        }

        return null;
    }

    /**
     * Calculate voucher discount
     */
    private function calculateVoucherDiscount(?array $voucher, float $subtotal): float
    {
        if (!$voucher) {
            return 0;
        }

        // Check minimum purchase
        if ($subtotal < $voucher['min_purchase_amount']) {
            return 0;
        }

        if ($voucher['discount_type'] === 'percentage') {
            $discount = $subtotal * ($voucher['discount_value'] / 100);
            // Apply max discount cap
            if ($voucher['max_discount_amount'] && $discount > $voucher['max_discount_amount']) {
                $discount = $voucher['max_discount_amount'];
            }
            return $discount;
        } else {
            // Fixed amount
            return min($voucher['discount_value'], $subtotal);
        }
    }

    /**
     * Mock vouchers data
     */
    // Mock vouchers method removed as it is no longer used
}
