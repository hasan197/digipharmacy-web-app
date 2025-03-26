<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@digipharmacy.com',
            'password' => Hash::make('password123'),
        ]);

        // Create pharmacist user
        User::create([
            'name' => 'Pharmacist',
            'email' => 'pharmacist@digipharmacy.com',
            'password' => Hash::make('password123'),
        ]);

        // Create cashier user
        User::create([
            'name' => 'Cashier',
            'email' => 'cashier@digipharmacy.com',
            'password' => Hash::make('password123'),
        ]);
    }
}
