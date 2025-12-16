#!/usr/bin/env php
<?php

/*
 * Quick Registration Test Script
 * Run with: php test-registration.php
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Registration Test ===\n\n";

// Test data
$testData = [
    'email' => 'test_' . time() . '@example.com',
    'password' => 'Test@123456',
    'full_name' => 'Test User',
    'date_of_birth' => '1990-01-01',
    'phone_number' => '1234567890',
];

echo "1. Testing with data:\n";
echo "   Email: {$testData['email']}\n";
echo "   Name: {$testData['full_name']}\n\n";

try {
    $authService = new \App\Modules\Auth\Services\AuthService();
    
    echo "2. Calling register method...\n";
    $result = $authService->register($testData);
    
    echo "   ✅ Registration returned successfully\n";
    echo "   User ID: " . ($result['user']->id ?? 'N/A') . "\n";
    echo "   Email: " . ($result['user']->email ?? 'N/A') . "\n\n";
    
    echo "3. Verifying in database...\n";
    $user = \App\Models\User::where('email', $testData['email'])->first();
    
    if ($user) {
        echo "   ✅ User found in database!\n";
        echo "   ID: {$user->id}\n";
        echo "   Email: {$user->email}\n";
        echo "   Role: {$user->role}\n";
        echo "   Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
        
        $profile = $user->profile;
        if ($profile) {
            echo "   ✅ Profile found!\n";
            echo "   Name: {$profile->full_name}\n";
        } else {
            echo "   ❌ Profile NOT found!\n";
        }
    } else {
        echo "   ❌ User NOT found in database!\n";
        echo "   This means the transaction was rolled back or not committed.\n";
    }
    
    echo "\n4. Checking total users in database...\n";
    $totalUsers = \App\Models\User::count();
    echo "   Total users: {$totalUsers}\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
