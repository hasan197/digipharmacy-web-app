<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SalesOrderDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_sales_order_detail()
    {
        $customer = Customer::factory()->create();
        $order = SalesOrder::factory()->create([
            'customer_id' => $customer->id
        ]);
        $product = Product::factory()->create();

        $detailData = [
            'sales_order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 25000,
            'subtotal' => 50000
        ];

        $detail = SalesOrderDetail::create($detailData);

        $this->assertDatabaseHas('sales_order_details', [
            'id' => $detail->id,
            'sales_order_id' => $order->id,
            'product_id' => $product->id
        ]);
    }

    public function test_sales_order_detail_belongs_to_sales_order()
    {
        $customer = Customer::factory()->create();
        $order = SalesOrder::factory()->create([
            'customer_id' => $customer->id
        ]);
        $product = Product::factory()->create();
        $detail = SalesOrderDetail::factory()->create([
            'sales_order_id' => $order->id,
            'product_id' => $product->id
        ]);

        $this->assertInstanceOf(SalesOrder::class, $detail->salesOrder);
        $this->assertEquals($order->id, $detail->salesOrder->id);
    }

    public function test_sales_order_detail_belongs_to_product()
    {
        $customer = Customer::factory()->create();
        $order = SalesOrder::factory()->create([
            'customer_id' => $customer->id
        ]);
        $product = Product::factory()->create();
        $detail = SalesOrderDetail::factory()->create([
            'sales_order_id' => $order->id,
            'product_id' => $product->id
        ]);

        $this->assertInstanceOf(Product::class, $detail->product);
        $this->assertEquals($product->id, $detail->product->id);
    }
}
