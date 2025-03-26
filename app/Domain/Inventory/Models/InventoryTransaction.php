<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Inventory\ValueObjects\InventoryTransactionType;
use App\Domain\Inventory\ValueObjects\StockLevel;
use DateTime;

class InventoryTransaction
{
    private ?int $id;
    private int $productId;
    private InventoryTransactionType $type;
    private int $quantity;
    private ?string $notes;
    private ?int $referenceId;
    private ?string $referenceType;
    private ?DateTime $createdAt;
    private ?DateTime $updatedAt;

    private function __construct()
    {
        // Private constructor to enforce factory method
    }

    public static function create(
        int $productId,
        InventoryTransactionType $type,
        int $quantity,
        ?string $notes = null,
        ?int $referenceId = null,
        ?string $referenceType = null,
        ?int $id = null,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ): self {
        $transaction = new self();
        $transaction->id = $id;
        $transaction->productId = $productId;
        $transaction->type = $type;
        $transaction->quantity = $quantity;
        $transaction->notes = $notes;
        $transaction->referenceId = $referenceId;
        $transaction->referenceType = $referenceType;
        $transaction->createdAt = $createdAt ?? new DateTime();
        $transaction->updatedAt = $updatedAt;

        return $transaction;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getType(): InventoryTransactionType
    {
        return $this->type;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getReferenceId(): ?int
    {
        return $this->referenceId;
    }

    public function getReferenceType(): ?string
    {
        return $this->referenceType;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'type' => $this->type->getValue(),
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'reference_id' => $this->referenceId,
            'reference_type' => $this->referenceType,
            'created_at' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
        ];
    }
} 