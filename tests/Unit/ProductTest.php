<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_product()
    {
        $category = Category::factory()->create();

        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 10000,
            'stock' => 100,
            'category_id' => $category->id
        ];

        $product = Product::create($productData);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Test Product'
        ]);
    }

    public function test_product_belongs_to_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id
        ]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }
}
