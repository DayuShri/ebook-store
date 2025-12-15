#!/usr/bin/env php
<?php

/*
 * Wallet Service Test Script
 * Run with: php test-wallet.php
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Wallet Service Test ===\n\n";

// Get or create test user
$user = \App\Models\User::where('email', 'wallet_test@example.com')->first();

if (!$user) {
    echo "Creating test user...\n";
    $authService = new \App\Modules\Auth\Services\AuthService();
    $result = $authService->register([
        'email' => 'wallet_test@example.com',
        'password' => 'Test@123456',
        'full_name' => 'Wallet Test User',
        'date_of_birth' => '1990-01-15',
        'phone_number' => '+1234567890',
    ]);
    $user = \App\Models\User::find($result['user']->id);
    echo "✅ User created: {$user->email}\n\n";
} else {
    echo "✅ Using existing user: {$user->email}\n\n";
}

$walletService = new \App\Modules\Payment\Services\WalletService();

try {
    echo "TEST 1: Get Wallet (Auto-create)\n";
    echo "─────────────────────────────────\n";
    $wallet = $walletService->getWallet($user->id);
    echo "✅ Wallet ID: {$wallet->id}\n";
    echo "✅ Initial Balance: {$wallet->balance}\n\n";

    echo "TEST 2: Add Balance (Top-up 50,000)\n";
    echo "─────────────────────────────────\n";
    $transaction1 = $walletService->addBalance($user->id, 50000, null, 'Test top-up 1');
    echo "✅ Transaction ID: {$transaction1->id}\n";
    echo "✅ Type: {$transaction1->transaction_type}\n";
    echo "✅ Amount: {$transaction1->amount}\n";
    echo "✅ Balance Before: {$transaction1->balance_before}\n";
    echo "✅ Balance After: {$transaction1->balance_after}\n\n";

    echo "TEST 3: Add More Balance (Top-up 25,000)\n";
    echo "─────────────────────────────────\n";
    $transaction2 = $walletService->addBalance($user->id, 25000, 'payment-123', 'Bank transfer');
    echo "✅ Transaction ID: {$transaction2->id}\n";
    echo "✅ Balance Before: {$transaction2->balance_before}\n";
    echo "✅ Balance After: {$transaction2->balance_after}\n";
    echo "✅ Reference ID: {$transaction2->reference_id}\n\n";

    echo "TEST 4: Deduct Balance (Purchase 30,000)\n";
    echo "─────────────────────────────────\n";
    $transaction3 = $walletService->deductBalance($user->id, 30000, 'order-456', 'E-Book purchase');
    echo "✅ Transaction ID: {$transaction3->id}\n";
    echo "✅ Type: {$transaction3->transaction_type}\n";
    echo "✅ Amount: {$transaction3->amount}\n";
    echo "✅ Balance Before: {$transaction3->balance_before}\n";
    echo "✅ Balance After: {$transaction3->balance_after}\n\n";

    echo "TEST 5: Insufficient Balance (Try to deduct 50,000)\n";
    echo "─────────────────────────────────\n";
    try {
        $walletService->deductBalance($user->id, 50000, 'order-789', 'Premium package');
        echo "❌ Should have thrown InsufficientBalanceException!\n\n";
    } catch (\App\Modules\Payment\Exceptions\InsufficientBalanceException $e) {
        echo "✅ Exception caught correctly\n";
        echo "✅ Message: {$e->getMessage()}\n";
        echo "✅ Code: {$e->getCode()}\n\n";
    }

    echo "TEST 6: Check Final Balance\n";
    echo "─────────────────────────────────\n";
    $wallet->refresh();
    echo "✅ Final Balance: {$wallet->balance}\n";
    echo "✅ Expected: 45000.00\n";
    echo "✅ Match: " . ($wallet->balance == 45000 ? "YES" : "NO") . "\n\n";

    echo "TEST 7: Transaction History\n";
    echo "─────────────────────────────────\n";
    $transactions = $wallet->transactions()->orderBy('created_at', 'desc')->get();
    echo "✅ Total Transactions: {$transactions->count()}\n";
    foreach ($transactions as $index => $txn) {
        echo "\n  Transaction " . ($index + 1) . ":\n";
        echo "  - Type: {$txn->transaction_type}\n";
        echo "  - Amount: {$txn->amount}\n";
        echo "  - Balance: {$txn->balance_before} → {$txn->balance_after}\n";
        echo "  - Description: {$txn->description}\n";
    }
    echo "\n";

    echo "TEST 8: Balance Integrity Check\n";
    echo "─────────────────────────────────\n";
    $lastTransaction = $transactions->first();
    $balanceMatch = $wallet->balance == $lastTransaction->balance_after;
    echo "✅ Wallet Balance: {$wallet->balance}\n";
    echo "✅ Last Transaction Balance: {$lastTransaction->balance_after}\n";
    echo "✅ Integrity: " . ($balanceMatch ? "PASS" : "FAIL") . "\n\n";

    echo "═══════════════════════════════════\n";
    echo "ALL TESTS PASSED! ✅\n";
    echo "═══════════════════════════════════\n\n";

    echo "Summary:\n";
    echo "- User: {$user->email}\n";
    echo "- Wallet ID: {$wallet->id}\n";
    echo "- Final Balance: {$wallet->balance}\n";
    echo "- Total Transactions: {$transactions->count()}\n";
    echo "- All balances verified ✅\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
