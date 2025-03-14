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

        if (!User::where('email', 'sales1@ptsmart.com')->exists()) {
            User::create([
                'name' => 'Sales User1',
                'email' => 'sales1@ptsmart.com',
                'password' => Hash::make('password'),
                'role' => 'sales',
            ]);
        }
        if (!User::where('email', 'sales2@ptsmart.com')->exists()) {
            User::create([
                'name' => 'Sales User2',
                'email' => 'sales2@ptsmart.com',
                'password' => Hash::make('password'),
                'role' => 'sales',
            ]);
        }

    }
}
