<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Library\Models\LibraryItem;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class LibraryTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user or create test user
        $user = User::first();
        
        if (!$user) {
            $user = User::create([
                'id' => Str::uuid()->toString(),
                'email' => 'test@example.com',
                'password_hash' => bcrypt('password'),
                'role' => 'user',
                'is_active' => true,
            ]);
            
            $user->profile()->create([
                'id' => Str::uuid()->toString(),
                'full_name' => 'Test User',
            ]);
        }

        // Create test books first
        $testBooks = [
            [
                'id' => 'book-001',
                'title' => 'The Clean Coder',
                'isbn' => '978-0137081073',
                'synopsis' => 'A guide to professional software development',
                'language' => 'en',
                'page_count' => 256,
                'price' => 150000,
                'publication_date' => '2011-05-13',
            ],
            [
                'id' => 'book-002',
                'title' => 'Atomic Habits',
                'isbn' => '978-0735211292',
                'synopsis' => 'An easy and proven way to build good habits and break bad ones',
                'language' => 'en',
                'page_count' => 320,
                'price' => 200000,
                'publication_date' => '2018-10-16',
            ],
            [
                'id' => 'book-003',
                'title' => 'Deep Work',
                'isbn' => '978-1455586691',
                'synopsis' => 'Rules for focused success in a distracted world',
                'language' => 'en',
                'page_count' => 296,
                'price' => 175000,
                'publication_date' => '2016-01-05',
            ],
        ];

        foreach ($testBooks as $bookData) {
            DB::table('books')->updateOrInsert(
                ['id' => $bookData['id']],
                array_merge($bookData, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Add books to user's library
        foreach ($testBooks as $bookData) {
            LibraryItem::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'book_id' => $bookData['id'],
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'order_id' => 'order-' . Str::random(8),
                    'status' => 'ACTIVE',
                    'granted_at' => now()->toDateTimeString(),
                ]
            );
        }

        $this->command->info('Library test data seeded successfully!');
        $this->command->info('User: ' . $user->email . ' (password: password)');
        $this->command->info('Books added: ' . count($testBooks));
    }
}
