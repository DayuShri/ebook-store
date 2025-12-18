<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'id' => Str::uuid(),
            'email' => 'admin@ebook-store.com',
            'password_hash' => Hash::make('Admin123!'), // Change this password in production
            'role' => 'admin',
            'is_active' => true,
            'last_login_at' => null,
        ]);

        // Create admin profile
        UserProfile::create([
            'id' => Str::uuid(),
            'user_id' => $admin->id,
            'full_name' => 'System Administrator',
            'phone' => null,
            'address' => null,
            'avatar_url' => null,
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@ebook-store.com');
        $this->command->info('Password: Admin123!');
        $this->command->warn('Please change the password after first login!');
    }
}
