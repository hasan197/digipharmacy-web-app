<?php

namespace App\Application\Contracts\Inventory;

use App\Domain\Inventory\Models\InventoryTransaction;
use DateTime;
use Illuminate\Support\Collection;

interface InventoryManagementServiceInterface
{
    /**
     * Record stock in transaction
     */
    public function recordStockIn(
        int $productId,
        int $quantity,
        ?string $notes = null,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): array;
    
    /**
     * Record stock out transaction
     */
    public function recordStockOut(
        int $productId,
        int $quantity,
        ?string $notes = null,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): array;
    
    /**
     * Adjust stock level for a product
     */
    public function adjustStock(
        int $productId,
        int $newStockLevel,
        ?string $notes = null
    ): array;
    
    /**
     * Get transaction history for a product
     */
    public function getProductTransactionHistory(int $productId): array;
    
    /**
     * Get transactions by date range
     */
    public function getTransactionsByDateRange(string $startDate, string $endDate): array;
    
    /**
     * Get products with low stock
     */
    public function getLowStockProducts(int $threshold = 10): array;
    
    /**
     * Get latest inventory transactions
     */
    public function getLatestTransactions(int $limit = 10): array;
    
    /**
     * Get transaction by ID
     */
    public function getTransactionById(int $id): array;
    
    /**
     * Get all products with stock information
     */
    public function getAllProductsWithStock(string $query = '', ?int $categoryId = null): array;
} 