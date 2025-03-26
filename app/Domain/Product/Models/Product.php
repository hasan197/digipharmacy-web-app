<?php

namespace App\Domain\Product\Models;

use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Product\ValueObjects\ProductStatus;
use DateTime;
use InvalidArgumentException;

class Product
{
    private ProductId $id;
    private string $name;
    private int $categoryId;
    private ?string $description;
    private float $price;
    private int $stock;
    private string $unit;
    private ?DateTime $expiryDate;
    private bool $requiresPrescription;
    private ProductStatus $status;
    private ?string $sku;
    private ?string $barcode;
    private ?float $costPrice;
    private ?DateTime $createdAt;
    private ?DateTime $updatedAt;

    private function __construct()
    {
        // Private constructor to enforce factory method
    }

    public static function create(
        string $name,
        int $categoryId,
        float $price,
        int $stock,
        string $unit,
        ?DateTime $expiryDate = null,
        ?string $description = null,
        bool $requiresPrescription = false,
        ?string $status = ProductStatus::ACTIVE,
        ?string $sku = null,
        ?string $barcode = null,
        ?float $costPrice = null,
        ?int $id = null,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ): self {
        $product = new self();
        $product->id = $id ? new ProductId($id) : new ProductId(0);
        $product->name = $name;
        $product->categoryId = $categoryId;
        $product->description = $description;
        $product->price = $price;
        $product->stock = $stock;
        $product->unit = $unit;
        $product->expiryDate = $expiryDate;
        $product->requiresPrescription = $requiresPrescription;
        $product->status = new ProductStatus($status);
        $product->sku = $sku;
        $product->barcode = $barcode;
        $product->costPrice = $costPrice;
        $product->createdAt = $createdAt;
        $product->updatedAt = $updatedAt;

        return $product;
    }

    public function getId(): ProductId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        if ($price < 0) {
            throw new InvalidArgumentException("Price cannot be negative");
        }
        $this->price = $price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): void
    {
        $this->unit = $unit;
    }

    public function getExpiryDate(): ?DateTime
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(?DateTime $expiryDate): void
    {
        $this->expiryDate = $expiryDate;
    }

    public function getRequiresPrescription(): bool
    {
        return $this->requiresPrescription;
    }

    public function setRequiresPrescription(bool $requiresPrescription): void
    {
        $this->requiresPrescription = $requiresPrescription;
    }

    public function getStatus(): ProductStatus
    {
        return $this->status;
    }

    public function setStatus(ProductStatus $status): void
    {
        $this->status = $status;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): void
    {
        $this->sku = $sku;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(?string $barcode): void
    {
        $this->barcode = $barcode;
    }

    public function getCostPrice(): ?float
    {
        return $this->costPrice;
    }

    public function setCostPrice(?float $costPrice): void
    {
        if ($costPrice !== null && $costPrice < 0) {
            throw new InvalidArgumentException("Cost price cannot be negative");
        }
        $this->costPrice = $costPrice;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function decrementStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("Quantity must be positive");
        }

        if ($this->stock < $quantity) {
            throw new InvalidArgumentException("Insufficient stock");
        }

        $this->stock -= $quantity;
    }

    public function incrementStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("Quantity must be positive");
        }

        $this->stock += $quantity;
    }

    public function updateStock(int $newStock): void
    {
        if ($newStock < 0) {
            throw new InvalidArgumentException("Stock cannot be negative");
        }

        $this->stock = $newStock;
    }

    public function calculateProfit(): ?float
    {
        if ($this->costPrice === null) {
            return null;
        }
        
        return $this->price - $this->costPrice;
    }

    public function calculateProfitMargin(): ?float
    {
        if ($this->costPrice === null || $this->costPrice === 0) {
            return null;
        }
        
        return ($this->price - $this->costPrice) / $this->costPrice * 100;
    }

    public function activate(): void
    {
        $this->status = ProductStatus::active();
    }

    public function deactivate(): void
    {
        $this->status = ProductStatus::inactive();
    }

    public function discontinue(): void
    {
        $this->status = ProductStatus::discontinued();
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'name' => $this->name,
            'category_id' => $this->categoryId,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'unit' => $this->unit,
            'expiry_date' => $this->expiryDate ? $this->expiryDate->format('Y-m-d') : null,
            'requires_prescription' => $this->requiresPrescription,
            'status' => $this->status->getValue(),
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'cost_price' => $this->costPrice,
            'profit' => $this->calculateProfit(),
            'profit_margin' => $this->calculateProfitMargin(),
            'created_at' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null
        ];
    }
}
