<?php

namespace App\Infrastructure\Inventory\Mappers;

use App\Domain\Inventory\Models\InventoryTransaction as DomainInventoryTransaction;
use App\Domain\Inventory\ValueObjects\InventoryTransactionType;
use App\Models\InventoryTransaction as EloquentInventoryTransaction;
use DateTime;

class InventoryTransactionMapper
{
    public function toDomain(EloquentInventoryTransaction $model): DomainInventoryTransaction
    {
        return DomainInventoryTransaction::create(
            $model->product_id,
            new InventoryTransactionType($model->type),
            $model->quantity,
            $model->notes,
            $model->reference_id,
            $model->reference_type,
            $model->id,
            $model->created_at ? new DateTime($model->created_at) : null,
            $model->updated_at ? new DateTime($model->updated_at) : null
        );
    }

    public function toPersistence(DomainInventoryTransaction $transaction): array
    {
        return [
            'product_id' => $transaction->getProductId(),
            'type' => $transaction->getType()->getValue(),
            'quantity' => $transaction->getQuantity(),
            'notes' => $transaction->getNotes(),
            'reference_id' => $transaction->getReferenceId(),
            'reference_type' => $transaction->getReferenceType()
        ];
    }
} 