<?php

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Models\Product;
use App\Domain\Product\ValueObjects\ProductId;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function findById(ProductId $id): ?Product;
    public function findByName(string $name): ?Product;
    public function save(Product $product): Product;
    public function update(Product $product): Product;
    public function delete(ProductId $productId): void;
    public function getAll(array $filters = []): Collection;
    public function getLowStockProducts(int $threshold = 10): Collection;
    public function getProductsByCategory(int $categoryId): Collection;
    public function getProductsByStatus(string $status): Collection;
    public function searchProducts(string $query): Collection;
}
