<?php

namespace App\Domain\Sales\Models;

class SalesOrderDetail
{
    private ?int $id;
    private ?int $salesOrderId;
    private int $productId;
    private int $quantity;
    private float $price;
    private float $subtotal;
    private ?\DateTime $createdAt;
    private ?\DateTime $updatedAt;

    public function __construct()
    {
        // Default constructor
    }

    public static function create(
        int $productId,
        int $quantity,
        float $price,
        float $subtotal,
        ?int $salesOrderId = null,
        ?int $id = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ): self {
        $detail = new self();
        $detail->id = $id;
        $detail->salesOrderId = $salesOrderId;
        $detail->productId = $productId;
        $detail->quantity = $quantity;
        $detail->price = $price;
        $detail->subtotal = $subtotal;
        $detail->createdAt = $createdAt;
        $detail->updatedAt = $updatedAt;

        return $detail;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getSalesOrderId(): ?int
    {
        return $this->salesOrderId;
    }

    public function setSalesOrderId(?int $salesOrderId): void
    {
        $this->salesOrderId = $salesOrderId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
