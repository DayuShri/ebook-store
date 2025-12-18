<?php

namespace App\Services\Frontend;

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

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
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
                    'subtotal' => $this->calculateItemPrice($book) * $item['quantity'],
                ];
            }
        }

        $subtotal = array_sum(array_column($enrichedItems, 'subtotal'));
        $discount = $this->calculateVoucherDiscount($cart['voucher'], $subtotal);

        return [
            'items' => $enrichedItems,
            'voucher' => $cart['voucher'],
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount,
            'item_count' => array_sum(array_column($enrichedItems, 'quantity')),
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
        
        $cart['items'] = array_values(array_filter($cart['items'], function($item) use ($bookId) {
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
        $vouchers = $this->getMockVouchers();
        $code = strtoupper(trim($code));
        
        foreach ($vouchers as $voucher) {
            if ($voucher['code'] === $code && $voucher['is_active']) {
                return $voucher;
            }
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
    private function getMockVouchers(): array
    {
        return [
            [
                'id' => 'voucher-1',
                'code' => 'DISKON10',
                'description' => 'Diskon 10% untuk semua buku',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'max_discount_amount' => 50000,
                'min_purchase_amount' => 100000,
                'is_active' => true,
            ],
            [
                'id' => 'voucher-2',
                'code' => 'HEMAT20K',
                'description' => 'Potongan Rp 20.000',
                'discount_type' => 'fixed',
                'discount_value' => 20000,
                'max_discount_amount' => null,
                'min_purchase_amount' => 150000,
                'is_active' => true,
            ],
            [
                'id' => 'voucher-3',
                'code' => 'WELCOME25',
                'description' => 'Diskon 25% untuk pengguna baru',
                'discount_type' => 'percentage',
                'discount_value' => 25,
                'max_discount_amount' => 75000,
                'min_purchase_amount' => 50000,
                'is_active' => true,
            ],
        ];
    }
}
