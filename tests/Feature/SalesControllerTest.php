<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SalesControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $customer;
    private $product;
    private $salesOrder;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test customer
        $this->customer = Customer::create([
            'name' => 'Test Customer',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'address' => 'Test Address'
        ]);

        // Create test category
        $category = Category::create([
            'name' => 'Test Category',
            'description' => 'Test Category Description'
        ]);

        // Create test product
        $this->product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'unit' => 'pcs',
            'price' => 10000,
            'stock' => 100,
            'category_id' => $category->id
        ]);

        // Create test sales order
        $this->salesOrder = SalesOrder::create([
            'customer_id' => $this->customer->id,
            'total' => 20000,
            'discount' => 0,
            'additional_fee' => 0,
            'grand_total' => 20000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'notes' => 'Test Notes'
        ]);

        // Create test sales order detail
        SalesOrderDetail::create([
            'sales_order_id' => $this->salesOrder->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 10000,
            'subtotal' => 20000
        ]);
    }

    public function test_can_get_sales_list()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/sales');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'customer',
                    'status',
                    'time',
                    'amount',
                    'itemCount',
                    'type'
                ]
            ]);
    }

    public function test_can_get_sale_details()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/sales/{$this->salesOrder->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'customer' => [
                    'name',
                    'phone',
                    'email',
                    'address'
                ],
                'items' => [
                    '*' => [
                        'id',
                        'product' => [
                            'name',
                            'unit'
                        ],
                        'quantity',
                        'price',
                        'subtotal'
                    ]
                ],
                'total',
                'discount',
                'additional_fee',
                'grand_total',
                'payment_method',
                'status',
                'notes',
                'created_at'
            ])
            ->assertJson([
                'customer' => [
                    'name' => 'Test Customer',
                    'phone' => '1234567890',
                    'email' => 'test@example.com',
                    'address' => 'Test Address'
                ],
                'items' => [
                    [
                        'product' => [
                            'name' => 'Test Product',
                            'unit' => 'pcs'
                        ],
                        'quantity' => 2,
                        'price' => 10000,
                        'subtotal' => 20000
                    ]
                ],
                'total' => 20000,
                'discount' => 0,
                'additional_fee' => 0,
                'grand_total' => 20000,
                'payment_method' => 'cash',
                'status' => 'completed',
                'notes' => 'Test Notes'
            ]);
    }

    public function test_returns_404_for_nonexistent_sale()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/sales/999999');

        $response->assertStatus(404);
    }

    public function test_unauthorized_user_cannot_access_sales()
    {
        $response = $this->getJson('/api/sales');
        $response->assertStatus(401);

        $response = $this->getJson("/api/sales/{$this->salesOrder->id}");
        $response->assertStatus(401);
    }
}
