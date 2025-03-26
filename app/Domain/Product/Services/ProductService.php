<?php

namespace App\Domain\Product\Services;

use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Product\ValueObjects\ProductStatus;
use DateTime;
use Illuminate\Support\Collection;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function findById(int $id): Product
    {
        $productId = new ProductId($id);
        $product = $this->productRepository->findById($productId);
        
        if (!$product) {
            throw new ProductNotFoundException($id);
        }
        
        return $product;
    }

    public function findByName(string $name): ?Product
    {
        return $this->productRepository->findByName($name);
    }

    public function createProduct(
        string $name,
        int $categoryId,
        float $price,
        int $stock,
        string $unit,
        ?DateTime $expiryDate = null,
        ?string $description = null,
        bool $requiresPrescription = false,
        string $status = ProductStatus::ACTIVE,
        ?string $sku = null,
        ?string $barcode = null,
        ?float $costPrice = null
    ): Product {
        $product = Product::create(
            $name,
            $categoryId,
            $price,
            $stock,
            $unit,
            $expiryDate,
            $description,
            $requiresPrescription,
            $status,
            $sku,
            $barcode,
            $costPrice
        );
        
        return $this->productRepository->save($product);
    }

    public function updateProduct(
        int $id,
        string $name,
        int $categoryId,
        float $price,
        string $unit,
        ?DateTime $expiryDate = null,
        ?string $description = null,
        bool $requiresPrescription = false,
        string $status = ProductStatus::ACTIVE,
        ?string $sku = null,
        ?string $barcode = null,
        ?float $costPrice = null
    ): Product {
        $product = $this->findById($id);
        
        $product->setName($name);
        $product->setCategoryId($categoryId);
        $product->setDescription($description);
        $product->setPrice($price);
        $product->setUnit($unit);
        $product->setExpiryDate($expiryDate);
        $product->setRequiresPrescription($requiresPrescription);
        $product->setStatus(new ProductStatus($status));
        $product->setSku($sku);
        $product->setBarcode($barcode);
        $product->setCostPrice($costPrice);
        
        return $this->productRepository->update($product);
    }

    public function deleteProduct(int $id): void
    {
        $productId = new ProductId($id);
        $product = $this->productRepository->findById($productId);
        
        if (!$product) {
            throw new ProductNotFoundException($id);
        }
        
        $this->productRepository->delete($productId);
    }

    public function changeProductStatus(int $id, string $status): Product
    {
        $product = $this->findById($id);
        $product->setStatus(new ProductStatus($status));
        
        return $this->productRepository->update($product);
    }

    public function getProductsByCategory(int $categoryId): Collection
    {
        return $this->productRepository->getProductsByCategory($categoryId);
    }

    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return $this->productRepository->getLowStockProducts($threshold);
    }

    public function getAllProducts(array $filters = []): Collection
    {
        return $this->productRepository->getAll($filters);
    }
} 