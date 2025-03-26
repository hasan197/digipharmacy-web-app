<?php

namespace App\Application\Contracts\Product;

interface ProductManagementServiceInterface
{
    /**
     * Get all products with optional filters
     */
    public function getAllProducts(array $filters = []): array;
    
    /**
     * Get product by ID
     */
    public function getProductById(int $id): array;
    
    /**
     * Create a new product
     */
    public function createProduct(array $data): array;
    
    /**
     * Update an existing product
     */
    public function updateProduct(int $id, array $data): array;
    
    /**
     * Delete a product
     */
    public function deleteProduct(int $id): array;
    
    /**
     * Change product status
     */
    public function changeProductStatus(int $id, string $status): array;
    
    /**
     * Get products by category
     */
    public function getProductsByCategory(int $categoryId): array;
    
    /**
     * Get products with low stock
     */
    public function getLowStockProducts(int $threshold = 10): array;
    
    /**
     * Search products
     */
    public function searchProducts(string $query): array;
} 