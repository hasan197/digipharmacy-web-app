<?php

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\Models\InventoryTransaction;
use App\Domain\Inventory\ValueObjects\InventoryTransactionType;
use DateTime;
use Illuminate\Support\Collection;

interface InventoryTransactionRepositoryInterface
{
    public function findById(int $id): ?InventoryTransaction;
    
    public function save(InventoryTransaction $transaction): InventoryTransaction;
    
    public function getTransactionsByProductId(int $productId): Collection;
    
    public function getTransactionsByType(InventoryTransactionType $type): Collection;
    
    public function getTransactionsByDateRange(DateTime $startDate, DateTime $endDate): Collection;
    
    public function getTransactionsByProductAndDateRange(
        int $productId, 
        DateTime $startDate, 
        DateTime $endDate
    ): Collection;
    
    public function getLatestTransactions(int $limit = 10): Collection;
    
    public function getTransactionsByReference(string $referenceType, int $referenceId): Collection;
} 