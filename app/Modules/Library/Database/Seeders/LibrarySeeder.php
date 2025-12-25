<?php

namespace App\Modules\Library\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Modules\Library\Models\BookFile;
use App\Models\User;

class LibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookId = Str::uuid()->toString();
        $bookData = [
            'id' => $bookId,
            'title' => 'Seeder Test Book',
            'price' => 0.00,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('books')->insert($bookData);

        $fileId = Str::uuid()->toString();
        // Create a BookFile record pointing to a sample public URL (replace if needed)
        BookFile::create([
            'id' => $fileId,
            'book_id' => $bookId,
            // Use a public dummy PDF for testing stream/redirect behavior
            'file_path' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'file_format' => 'pdf',
            'file_size_mb' => 0.02,
        ]);

        // Ensure we have a user to attach the order to
        $user = User::first();
        if (! $user) {
            $user = User::factory()->create([
                'email' => 'seed-user@example.com',
            ]);
        }

        // Create a paid order and order item for this book so HMVC grant can reference a real order
        $orderId = Str::uuid()->toString();
        $orderNumber = 'ORD-' . strtoupper(substr(Str::random(8), 0, 8));
        $totalAmount = 0.00; // price is 0 in book seeder
        $finalAmount = $totalAmount;

        DB::table('orders')->insert([
            'id' => $orderId,
            'order_number' => $orderNumber,
            'user_id' => $user->id,
            'total_amount' => $totalAmount,
            'discount_amount' => 0,
            'final_amount' => $finalAmount,
            'voucher_id' => null,
            'status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert order item
        DB::table('order_items')->insert([
            'id' => Str::uuid()->toString(),
            'order_id' => $orderId,
            'book_id' => $bookId,
            'quantity' => 1,
            'unit_price' => 0.00,
            'subtotal' => 0.00,
        ]);

        // Informational output for the developer running the seeder
        if ($this->command) {
            $this->command->info('Library seeder finished.');
            $this->command->info('BOOK_ID: ' . $bookId);
            $this->command->info('BOOK_FILE_ID: ' . $fileId);
            $this->command->info('SAMPLE_ORDER_ID: ' . $orderId);
        }
    }
}
