<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\InventoryTransaction;
use App\Domain\Inventory\Repositories\InventoryTransactionRepositoryInterface;
use App\Domain\Inventory\ValueObjects\InventoryTransactionType;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use DateTime;
use Illuminate\Support\Collection;

class InventoryManagementService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly InventoryTransactionRepositoryInterface $transactionRepository
    ) {}

    public function recordStockIn(
        int $productId,
        int $quantity,
        ?string $notes = null,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): InventoryTransaction {
        $product = $this->productRepository->findById($productId);
        
        if (!$product) {
            throw new \InvalidArgumentException("Product with ID {$productId} not found");
        }
        
        // Update product stock
        $product->incrementStock($quantity);
        $this->productRepository->update($product);
        
        // Create transaction record
        $transaction = InventoryTransaction::create(
            $productId,
            new InventoryTransactionType(InventoryTransactionType::STOCK_IN),
            $quantity,
            $notes,
            $referenceId,
            $referenceType
        );
        
        return $this->transactionRepository->save($transaction);
    }
    
    public function recordStockOut(
        int $productId,
        int $quantity,
        ?string $notes = null,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): InventoryTransaction {
        $product = $this->productRepository->findById($productId);
        
        if (!$product) {
            throw new \InvalidArgumentException("Product with ID {$productId} not found");
        }
        
        // Check if there's enough stock
        if ($product->getStock() < $quantity) {
            throw new InsufficientStockException($productId, $quantity, $product->getStock());
        }
        
        // Update product stock
        $product->decrementStock($quantity);
        $this->productRepository->update($product);
        
        // Create transaction record
        $transaction = InventoryTransaction::create(
            $productId,
            new InventoryTransactionType(InventoryTransactionType::STOCK_OUT),
            $quantity,
            $notes,
            $referenceId,
            $referenceType
        );
        
        return $this->transactionRepository->save($transaction);
    }
    
    public function adjustStock(
        int $productId,
        int $newStockLevel,
        ?string $notes = null
    ): InventoryTransaction {
        $product = $this->productRepository->findById($productId);
        
        if (!$product) {
            throw new \InvalidArgumentException("Product with ID {$productId} not found");
        }
        
        $currentStock = $product->getStock();
        $adjustmentQuantity = abs($newStockLevel - $currentStock);
        
        // Update product stock
        $product->updateStock($newStockLevel);
        $this->productRepository->update($product);
        
        // Create transaction record
        $transaction = InventoryTransaction::create(
            $productId,
            new InventoryTransactionType(InventoryTransactionType::ADJUSTMENT),
            $adjustmentQuantity,
            $notes ?? "Stock adjusted from {$currentStock} to {$newStockLevel}"
        );
        
        return $this->transactionRepository->save($transaction);
    }
    
    public function getProductTransactionHistory(int $productId): Collection
    {
        return $this->transactionRepository->getTransactionsByProductId($productId);
    }
    
    public function getTransactionsByDateRange(DateTime $startDate, DateTime $endDate): Collection
    {
        return $this->transactionRepository->getTransactionsByDateRange($startDate, $endDate);
    }
    
    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return $this->productRepository->getLowStockProducts($threshold);
    }
    
    public function getLatestTransactions(int $limit = 10): Collection
    {
        return $this->transactionRepository->getLatestTransactions($limit);
    }
    
    public function findById(int $id): ?InventoryTransaction
    {
        return $this->transactionRepository->findById($id);
    }
    
    public function getAllProductsWithStock(string $query = '', ?int $categoryId = null): Collection
    {
        $filters = [];
        
        if (!empty($query)) {
            $filters['query'] = $query;
        }
        
        if ($categoryId !== null) {
            $filters['category_id'] = $categoryId;
        }
        
        return $this->productRepository->getAll($filters);
    }
} 