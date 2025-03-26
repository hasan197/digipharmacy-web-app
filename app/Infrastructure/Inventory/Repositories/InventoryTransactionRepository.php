<?php

namespace App\Infrastructure\Inventory\Repositories;

use App\Domain\Inventory\Models\InventoryTransaction;
use App\Domain\Inventory\Repositories\InventoryTransactionRepositoryInterface;
use App\Domain\Inventory\ValueObjects\InventoryTransactionType;
use App\Infrastructure\Inventory\Mappers\InventoryTransactionMapper;
use App\Models\InventoryTransaction as EloquentInventoryTransaction;
use DateTime;
use Illuminate\Support\Collection;

class InventoryTransactionRepository implements InventoryTransactionRepositoryInterface
{
    public function __construct(
        private readonly InventoryTransactionMapper $mapper
    ) {}

    public function findById(int $id): ?InventoryTransaction
    {
        $model = EloquentInventoryTransaction::find($id);
        
        if (!$model) {
            return null;
        }
        
        return $this->mapper->toDomain($model);
    }
    
    public function save(InventoryTransaction $transaction): InventoryTransaction
    {
        $data = $this->mapper->toPersistence($transaction);
        
        if ($transaction->getId() === null) {
            // Create new record
            $model = EloquentInventoryTransaction::create($data);
        } else {
            // Update existing record
            $model = EloquentInventoryTransaction::find($transaction->getId());
            $model->update($data);
        }
        
        return $this->mapper->toDomain($model);
    }
    
    public function getTransactionsByProductId(int $productId): Collection
    {
        $models = EloquentInventoryTransaction::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return $models->map(fn ($model) => $this->mapper->toDomain($model));
    }
    
    public function getTransactionsByType(InventoryTransactionType $type): Collection
    {
        $models = EloquentInventoryTransaction::where('type', $type->getValue())
            ->orderBy('created_at', 'desc')
            ->get();
            
        return $models->map(fn ($model) => $this->mapper->toDomain($model));
    }
    
    public function getTransactionsByDateRange(DateTime $startDate, DateTime $endDate): Collection
    {
        $models = EloquentInventoryTransaction::whereBetween('created_at', [
                $startDate->format('Y-m-d H:i:s'),
                $endDate->format('Y-m-d H:i:s')
            ])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return $models->map(fn ($model) => $this->mapper->toDomain($model));
    }
    
    public function getTransactionsByProductAndDateRange(
        int $productId, 
        DateTime $startDate, 
        DateTime $endDate
    ): Collection {
        $models = EloquentInventoryTransaction::where('product_id', $productId)
            ->whereBetween('created_at', [
                $startDate->format('Y-m-d H:i:s'),
                $endDate->format('Y-m-d H:i:s')
            ])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return $models->map(fn ($model) => $this->mapper->toDomain($model));
    }
    
    public function getLatestTransactions(int $limit = 10): Collection
    {
        $models = EloquentInventoryTransaction::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
            
        return $models->map(fn ($model) => $this->mapper->toDomain($model));
    }
    
    public function getTransactionsByReference(string $referenceType, int $referenceId): Collection
    {
        $models = EloquentInventoryTransaction::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return $models->map(fn ($model) => $this->mapper->toDomain($model));
    }
} 