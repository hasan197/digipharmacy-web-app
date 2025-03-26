<?php

namespace Database\Factories;

use App\Models\SalesOrderDetail;
use App\Models\SalesOrder;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderDetailFactory extends Factory
{
    protected $model = SalesOrderDetail::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $price = fake()->numberBetween(1000, 100000);
        $subtotal = $quantity * $price;

        return [
            'sales_order_id' => SalesOrder::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $subtotal,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
