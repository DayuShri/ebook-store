<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Library\Database\Seeders\LibrarySeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        
        // Create a basic test user compatible with App\Models\User fields
        User::create([
            'email' => 'test@example.com',
            'password_hash' => bcrypt('password'),
            'role' => 'user',
            'is_active' => true,
        ]);
        $this->call([LibrarySeeder::class]);
    }
}
