<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        $existingAdmin = User::where('email', 'admin@ebook.test')->first();
        
        if ($existingAdmin) {
            $this->command->info('Admin user already exists, skipping...');
            return;
        }

        // Create admin user
        $adminId = (string) Str::uuid();
        
        $admin = User::create([
            'id' => $adminId,
            'email' => 'admin@ebook.test',
            'password_hash' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
            'last_login_at' => now(),
        ]);

        // Create admin profile
        UserProfile::create([
            'id' => (string) Str::uuid(),
            'user_id' => $adminId,
            'full_name' => 'Administrator',
            'phone_number' => '081234567890',
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@ebook.test');
        $this->command->info('Password: password123');
    }
}
