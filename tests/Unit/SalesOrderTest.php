<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SalesOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_sales_order()
    {
        $customer = Customer::factory()->create();

        $orderData = [
            'customer_id' => $customer->id,
            'total' => 50000,
            'discount' => 0,
            'additional_fee' => 0,
            'grand_total' => 50000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'total' => 50000,
            'discount' => 0,
            'additional_fee' => 0,
            'grand_total' => 50000,
            'payment_method' => 'cash',
            'status' => 'completed'
        ];

        $order = SalesOrder::create($orderData);

        $this->assertDatabaseHas('sales_orders', [
            'id' => $order->id,
            'customer_id' => $customer->id,
            'total' => 50000,
            'discount' => 0,
            'additional_fee' => 0,
            'grand_total' => 50000,
            'payment_method' => 'cash',
            'status' => 'completed'
        ]);
    }

    public function test_sales_order_has_many_details()
    {
        $customer = Customer::factory()->create();
        $order = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'total' => 50000,
            'discount' => 0,
            'additional_fee' => 0,
            'grand_total' => 50000,
            'payment_method' => 'cash',
            'status' => 'completed'
        ]);

        $product = Product::factory()->create();
        
        $detail = SalesOrderDetail::create([
            'sales_order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 25000,
            'subtotal' => 50000
        ]);

        $this->assertTrue($order->details->contains($detail));
        $this->assertEquals(1, $order->details->count());
    }

    public function test_sales_order_belongs_to_customer()
    {
        $customer = Customer::factory()->create();
        $order = SalesOrder::factory()->create([
            'customer_id' => $customer->id,
            'total' => 50000,
            'discount' => 0,
            'additional_fee' => 0,
            'grand_total' => 50000,
            'payment_method' => 'cash',
            'status' => 'completed'
        ]);

        $this->assertInstanceOf(Customer::class, $order->customer);
        $this->assertEquals($customer->id, $order->customer->id);
    }
}
