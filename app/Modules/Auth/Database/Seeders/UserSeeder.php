<?php

namespace App\Modules\Auth\Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'email' => 'admin@example.com',
            'password_hash' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create admin profile
        UserProfile::create([
            'user_id' => $admin->id,
            'full_name' => 'System Administrator',
            'date_of_birth' => '1990-01-01',
            'phone_number' => '0000000000',
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: admin123');
    }
}
