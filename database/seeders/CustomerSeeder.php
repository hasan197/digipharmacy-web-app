<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $customers = [
            [
                'name' => 'John Doe',
                'phone' => '081234567890',
                'email' => 'john@example.com',
                'address' => 'Jl. Contoh No. 1'
            ],
            [
                'name' => 'Jane Smith',
                'phone' => '081234567891',
                'email' => 'jane@example.com',
                'address' => 'Jl. Contoh No. 2'
            ],
            [
                'name' => 'Bob Johnson',
                'phone' => '081234567892',
                'email' => 'bob@example.com',
                'address' => 'Jl. Contoh No. 3'
            ]
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
