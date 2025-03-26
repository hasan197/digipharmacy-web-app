<?php

namespace App\Domain\Sales\Models;

use App\Domain\Sales\ValueObjects\SalesOrderId;
use App\Domain\Sales\ValueObjects\PaymentDetails;
use Illuminate\Support\Collection;

class SalesOrder
{
    private ?int $id;
    private ?string $invoiceNumber;
    private ?int $customerId;
    private ?string $customerName;
    private ?string $customerPhone;
    private float $total;
    private float $discount;
    private float $additionalFee;
    private float $grandTotal;
    private string $paymentMethod;
    private PaymentDetails $paymentDetails;
    private string $status;
    private ?string $notes;
    private Collection $orderDetails;
    private ?\DateTime $createdAt;
    private ?\DateTime $updatedAt;

    public function __construct()
    {
        $this->orderDetails = collect();
    }

    public static function create(
        ?int $customerId,
        ?string $customerName,
        ?string $customerPhone,
        float $total,
        float $discount,
        float $additionalFee,
        float $grandTotal,
        string $paymentMethod,
        PaymentDetails $paymentDetails,
        string $status,
        ?string $notes = null,
        ?string $invoiceNumber = null,
        ?int $id = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ): self {
        $salesOrder = new self();
        $salesOrder->id = $id;
        $salesOrder->invoiceNumber = $invoiceNumber;
        $salesOrder->customerId = $customerId;
        $salesOrder->customerName = $customerName;
        $salesOrder->customerPhone = $customerPhone;
        $salesOrder->total = $total;
        $salesOrder->discount = $discount;
        $salesOrder->additionalFee = $additionalFee;
        $salesOrder->grandTotal = $grandTotal;
        $salesOrder->paymentMethod = $paymentMethod;
        $salesOrder->paymentDetails = $paymentDetails;
        $salesOrder->status = $status;
        $salesOrder->notes = $notes;
        $salesOrder->createdAt = $createdAt;
        $salesOrder->updatedAt = $updatedAt;

        return $salesOrder;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): void
    {
        $this->invoiceNumber = $invoiceNumber;
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function getCustomerPhone(): ?string
    {
        return $this->customerPhone;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function getAdditionalFee(): float
    {
        return $this->additionalFee;
    }

    public function getGrandTotal(): float
    {
        return $this->grandTotal;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getPaymentDetails(): PaymentDetails
    {
        return $this->paymentDetails;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(SalesOrderDetail $orderDetail): void
    {
        $this->orderDetails->push($orderDetail);
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
