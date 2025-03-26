<?php

namespace App\Infrastructure\Product\Repositories;

use App\Domain\Product\Models\Product;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\ValueObjects\ProductId;
use App\Infrastructure\Product\Mappers\ProductMapper;
use App\Models\Product as EloquentProduct;
use Illuminate\Support\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly ProductMapper $mapper
    ) {}

    public function findById(ProductId $id): ?Product
    {
        $product = EloquentProduct::with('category')->find($id->getValue());
        return $product ? $this->mapper->toDomain($product) : null;
    }

    public function findByName(string $name): ?Product
    {
        $product = EloquentProduct::with('category')
            ->where('name', 'like', "%{$name}%")
            ->first();
        return $product ? $this->mapper->toDomain($product) : null;
    }

    public function save(Product $product): Product
    {
        $eloquentProduct = $this->mapper->toEloquent($product);
        $eloquentProduct->save();
        
        // Reload the model with relationships
        $eloquentProduct = EloquentProduct::with('category')->find($eloquentProduct->id);
        
        return $this->mapper->toDomain($eloquentProduct);
    }

    public function update(Product $product): Product
    {
        $eloquentProduct = $this->mapper->toEloquent($product);
        $eloquentProduct->save();
        
        // Reload the model with relationships
        $eloquentProduct = EloquentProduct::with('category')->find($eloquentProduct->id);
        
        return $this->mapper->toDomain($eloquentProduct);
    }

    public function delete(ProductId $productId): void
    {
        EloquentProduct::destroy($productId->getValue());
    }

    public function getAll(array $filters = []): Collection
    {
        $query = EloquentProduct::with('category');
        
        if (isset($filters['query']) && !empty($filters['query'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['query']}%")
                  ->orWhere('description', 'like', "%{$filters['query']}%")
                  ->orWhere('sku', 'like', "%{$filters['query']}%")
                  ->orWhere('barcode', 'like', "%{$filters['query']}%");
            });
        }
        
        if (isset($filters['category_id']) && $filters['category_id'] !== null) {
            $query->where('category_id', $filters['category_id']);
        }
        
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['min_price']) && $filters['min_price'] !== null) {
            $query->where('price', '>=', $filters['min_price']);
        }
        
        if (isset($filters['max_price']) && $filters['max_price'] !== null) {
            $query->where('price', '<=', $filters['max_price']);
        }
        
        if (isset($filters['requires_prescription'])) {
            $query->where('requires_prescription', $filters['requires_prescription']);
        }
        
        if (isset($filters['sort_by']) && !empty($filters['sort_by'])) {
            $direction = isset($filters['sort_direction']) && strtolower($filters['sort_direction']) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($filters['sort_by'], $direction);
        } else {
            $query->orderBy('name', 'asc');
        }
        
        $products = $query->get();
        
        return $products->map(function ($product) {
            return $this->mapper->toDomain($product);
        });
    }

    public function getLowStockProducts(int $threshold = 10): Collection
    {
        $products = EloquentProduct::with('category')
            ->where('stock', '<=', $threshold)
            ->where('status', '!=', 'discontinued')
            ->orderBy('stock', 'asc')
            ->get();
        
        return $products->map(function ($product) {
            return $this->mapper->toDomain($product);
        });
    }

    public function getProductsByCategory(int $categoryId): Collection
    {
        $products = EloquentProduct::with('category')
            ->where('category_id', $categoryId)
            ->orderBy('name', 'asc')
            ->get();
        
        return $products->map(function ($product) {
            return $this->mapper->toDomain($product);
        });
    }

    public function getProductsByStatus(string $status): Collection
    {
        $products = EloquentProduct::with('category')
            ->where('status', $status)
            ->orderBy('name', 'asc')
            ->get();
        
        return $products->map(function ($product) {
            return $this->mapper->toDomain($product);
        });
    }

    public function searchProducts(string $query): Collection
    {
        $products = EloquentProduct::with('category')
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->orWhere('barcode', 'like', "%{$query}%")
            ->orderBy('name', 'asc')
            ->get();
        
        return $products->map(function ($product) {
            return $this->mapper->toDomain($product);
        });
    }
}
