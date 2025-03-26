<?php

namespace Database\Factories;

use App\Models\SalesOrder;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(10000, 1000000);
        $discount = fake()->numberBetween(0, 10000);
        $additionalFee = fake()->numberBetween(0, 5000);
        $grandTotal = $total - $discount + $additionalFee;

        return [
            'customer_id' => Customer::factory(),
            'total' => $total,
            'discount' => $discount,
            'additional_fee' => $additionalFee,
            'grand_total' => $grandTotal,
            'payment_method' => fake()->randomElement(['cash', 'credit_card', 'debit_card']),
            'status' => fake()->randomElement(['pending', 'completed', 'cancelled']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
