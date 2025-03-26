<?php

namespace App\Application\Services;

use App\Application\Contracts\Product\ProductManagementServiceInterface;
use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Services\ProductService;
use DateTime;
use Exception;
use Illuminate\Support\Collection;

class ProductManagementService implements ProductManagementServiceInterface
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    /**
     * Get all products with optional filters
     */
    public function getAllProducts(array $filters = []): array
    {
        try {
            $products = $this->productService->getAllProducts($filters);
            
            return [
                'success' => true,
                'data' => $this->mapProductsToArray($products)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get product by ID
     */
    public function getProductById(int $id): array
    {
        try {
            $product = $this->productService->findById($id);
            
            return [
                'success' => true,
                'data' => $product->toArray()
            ];
        } catch (ProductNotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while retrieving the product'
            ];
        }
    }
    
    /**
     * Create a new product
     */
    public function createProduct(array $data): array
    {
        try {
            $expiryDate = isset($data['expiry_date']) ? new DateTime($data['expiry_date']) : null;
            
            $product = $this->productService->createProduct(
                $data['name'],
                $data['category_id'],
                $data['price'],
                $data['stock'] ?? 0,
                $data['unit'],
                $expiryDate,
                $data['description'] ?? null,
                $data['requires_prescription'] ?? false,
                $data['status'] ?? 'active',
                $data['sku'] ?? null,
                $data['barcode'] ?? null,
                $data['cost_price'] ?? null
            );
            
            return [
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->toArray()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing product
     */
    public function updateProduct(int $id, array $data): array
    {
        try {
            $expiryDate = isset($data['expiry_date']) ? new DateTime($data['expiry_date']) : null;
            
            $product = $this->productService->updateProduct(
                $id,
                $data['name'],
                $data['category_id'],
                $data['price'],
                $data['unit'],
                $expiryDate,
                $data['description'] ?? null,
                $data['requires_prescription'] ?? false,
                $data['status'] ?? 'active',
                $data['sku'] ?? null,
                $data['barcode'] ?? null,
                $data['cost_price'] ?? null
            );
            
            return [
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->toArray()
            ];
        } catch (ProductNotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a product
     */
    public function deleteProduct(int $id): array
    {
        try {
            $this->productService->deleteProduct($id);
            
            return [
                'success' => true,
                'message' => 'Product deleted successfully'
            ];
        } catch (ProductNotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Change product status
     */
    public function changeProductStatus(int $id, string $status): array
    {
        try {
            $product = $this->productService->changeProductStatus($id, $status);
            
            return [
                'success' => true,
                'message' => 'Product status updated successfully',
                'data' => $product->toArray()
            ];
        } catch (ProductNotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get products by category
     */
    public function getProductsByCategory(int $categoryId): array
    {
        try {
            $products = $this->productService->getProductsByCategory($categoryId);
            
            return [
                'success' => true,
                'data' => $this->mapProductsToArray($products)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get products with low stock
     */
    public function getLowStockProducts(int $threshold = 10): array
    {
        try {
            $products = $this->productService->getLowStockProducts($threshold);
            
            return [
                'success' => true,
                'data' => $this->mapProductsToArray($products)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Search products
     */
    public function searchProducts(string $query): array
    {
        try {
            $products = $this->productService->getAllProducts(['query' => $query]);
            
            return [
                'success' => true,
                'data' => $this->mapProductsToArray($products)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Map a collection of products to an array
     */
    private function mapProductsToArray(Collection $products): array
    {
        return $products->map(function (Product $product) {
            return $product->toArray();
        })->toArray();
    }
} 