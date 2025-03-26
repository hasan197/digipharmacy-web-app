<?php

namespace App\Application\Services;

use App\Application\Contracts\Inventory\InventoryManagementServiceInterface;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Exceptions\InventoryTransactionNotFoundException;
use App\Domain\Inventory\Models\InventoryTransaction;
use App\Domain\Inventory\Services\InventoryManagementService as DomainInventoryService;
use DateTime;
use Exception;
use Illuminate\Support\Collection;

class InventoryManagementService implements InventoryManagementServiceInterface
{
    public function __construct(
        private readonly DomainInventoryService $domainInventoryService
    ) {}

    /**
     * Record stock in transaction
     */
    public function recordStockIn(
        int $productId,
        int $quantity,
        ?string $notes = null,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): array {
        try {
            $transaction = $this->domainInventoryService->recordStockIn(
                $productId,
                $quantity,
                $notes,
                $referenceId,
                $referenceType
            );
            
            return [
                'success' => true,
                'message' => 'Stock in recorded successfully',
                'data' => $transaction->toArray()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Record stock out transaction
     */
    public function recordStockOut(
        int $productId,
        int $quantity,
        ?string $notes = null,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): array {
        try {
            $transaction = $this->domainInventoryService->recordStockOut(
                $productId,
                $quantity,
                $notes,
                $referenceId,
                $referenceType
            );
            
            return [
                'success' => true,
                'message' => 'Stock out recorded successfully',
                'data' => $transaction->toArray()
            ];
        } catch (InsufficientStockException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'product_id' => $e->getProductId(),
                'requested_quantity' => $e->getRequestedQuantity(),
                'available_quantity' => $e->getAvailableQuantity()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Adjust stock level for a product
     */
    public function adjustStock(
        int $productId,
        int $newStockLevel,
        ?string $notes = null
    ): array {
        try {
            $transaction = $this->domainInventoryService->adjustStock(
                $productId,
                $newStockLevel,
                $notes
            );
            
            return [
                'success' => true,
                'message' => 'Stock adjusted successfully',
                'data' => $transaction->toArray()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get transaction history for a product
     */
    public function getProductTransactionHistory(int $productId): array
    {
        try {
            $transactions = $this->domainInventoryService->getProductTransactionHistory($productId);
            
            return [
                'success' => true,
                'data' => $transactions->map(fn (InventoryTransaction $transaction) => $transaction->toArray())->toArray()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get transactions by date range
     */
    public function getTransactionsByDateRange(string $startDate, string $endDate): array
    {
        try {
            $startDateTime = new DateTime($startDate);
            $endDateTime = new DateTime($endDate);
            
            $transactions = $this->domainInventoryService->getTransactionsByDateRange($startDateTime, $endDateTime);
            
            return [
                'success' => true,
                'data' => $transactions->map(fn (InventoryTransaction $transaction) => $transaction->toArray())->toArray()
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
            $products = $this->domainInventoryService->getLowStockProducts($threshold);
            
            return [
                'success' => true,
                'data' => $products->toArray()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get latest inventory transactions
     */
    public function getLatestTransactions(int $limit = 10): array
    {
        try {
            $transactions = $this->domainInventoryService->getLatestTransactions($limit);
            
            return [
                'success' => true,
                'data' => $transactions->map(fn (InventoryTransaction $transaction) => $transaction->toArray())->toArray()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get transaction by ID
     */
    public function getTransactionById(int $id): array
    {
        try {
            $transaction = $this->domainInventoryService->findById($id);
            
            if (!$transaction) {
                throw new InventoryTransactionNotFoundException($id);
            }
            
            return [
                'success' => true,
                'data' => $transaction->toArray()
            ];
        } catch (InventoryTransactionNotFoundException $e) {
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
     * Get all products with stock information
     */
    public function getAllProductsWithStock(string $query = '', ?int $categoryId = null): array
    {
        try {
            $products = $this->domainInventoryService->getAllProductsWithStock($query, $categoryId);
            
            return [
                'success' => true,
                'data' => $products->toArray()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
} 