<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserPasswordSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@example.com')->first();
        
        if ($user) {
            $user->update([
                'password_hash' => Hash::make('password'),
            ]);
            
            $this->command->info('Password updated for user@example.com');
            $this->command->info('Email: user@example.com');
            $this->command->info('Password: password');
        } else {
            $this->command->error('User not found!');
        }
    }
}
