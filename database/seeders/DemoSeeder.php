<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // === USER DUMMY ===
        $userId = Str::uuid();

        DB::table('users')->insert([
            'id' => $userId,
            'email' => 'demo@user.com',
            'password_hash' => bcrypt('password'),
            'role' => 'user',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // === BOOK DUMMY (minimal, karena catalog dummy) ===
        $bookId = Str::uuid();

        DB::table('books')->insert([
            'id' => $bookId,
            'title' => 'Buku Dummy Order Service',
            'price' => 100000,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // === VOUCHER DUMMY ===
        $voucherId = Str::uuid();

        DB::table('vouchers')->insert([
            'id' => $voucherId,
            'code' => 'DISKON10',
            'description' => 'Diskon 10%',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_purchase_amount' => 50000,
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(7),
            'created_at' => now(),
        ]);
    }
}
