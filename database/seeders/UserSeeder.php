<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email', 'admin@ptsmart.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@ptsmart.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
        }

        if (!User::where('email', 'manager@ptsmart.com')->exists()) {
            User::create([
                'name' => 'Manager User',
                'email' => 'manager@ptsmart.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]);
        }

        if (!User::where('email', 'sales@ptsmart.com')->exists()) {
            User::create([
                'name' => 'Sales User',
                'email' => 'sales@ptsmart.com',
                'password' => Hash::make('password'),
                'role' => 'sales',
            ]);
        }

    }
}
