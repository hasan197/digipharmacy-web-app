<?php

namespace App\Domain\Sales\Services;

use App\Application\Contracts\Sales\SalesManagementServiceInterface;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Models\SalesOrderDetail;
use App\Domain\Sales\Repositories\SalesOrderRepositoryInterface;
use App\Domain\Sales\ValueObjects\PaymentDetails;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Services\LogService;
use Illuminate\Support\Collection;
use App\Domain\Sales\Exceptions\InsufficientStockException;
use App\Domain\Sales\Exceptions\SalesOrderNotFoundException;

class SalesManagementService implements SalesManagementServiceInterface
{
    private SalesOrderRepositoryInterface $salesOrderRepository;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        SalesOrderRepositoryInterface $salesOrderRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->salesOrderRepository = $salesOrderRepository;
        $this->productRepository = $productRepository;
    }

    public function createSalesOrder(array $data): array
    {
        // Validate stock and calculate totals
        $subtotal = 0;
        $orderDetails = [];
        
        foreach ($data['items'] as $item) {
            // Create a ProductId value object from the primitive integer
            $productId = new \App\Domain\Product\ValueObjects\ProductId($item['product_id']);
            $product = $this->productRepository->findById($productId);
            
            if (!$product) {
                throw new \Exception("Product not found with ID: {$item['product_id']}");
            }
            
            // Check if stock is sufficient
            if ($product->getStock() < $item['quantity']) {
                throw new InsufficientStockException(
                    "Stock tidak cukup untuk produk {$product->getName()}. Tersedia: {$product->getStock()}, Diminta: {$item['quantity']}"
                );
            }
            
            $itemSubtotal = $product->getPrice() * $item['quantity'];
            $subtotal += $itemSubtotal;
            
            // Create order detail
            $orderDetail = SalesOrderDetail::create(
                $item['product_id'],
                $item['quantity'],
                $product->getPrice(),
                $itemSubtotal
            );
            
            $orderDetails[] = $orderDetail;
            
            // Update product stock
            $product->decrementStock($item['quantity']);
            $this->productRepository->save($product);
            
            // Log stock update
            LogService::pos('Product stock updated after sale', [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'quantity_sold' => $item['quantity'],
                'new_stock' => $product->getStock(),
                'sale_id' => null // Will be updated after sale is created
            ]);
        }
        
        $discount = 0; // We can implement discount logic later
        $additionalFee = 0; // We can implement additional fee logic later
        $grandTotal = $subtotal - $discount + $additionalFee;
        
        // Create payment details value object
        $paymentDetails = PaymentDetails::fromArray($data['payment_details']);
        
        // Create sales order
        $salesOrder = SalesOrder::create(
            $data['customer_id'] ?? null,
            $data['customer_name'] ?? '',
            $data['customer_phone'] ?? '',
            $subtotal,
            $discount,
            $additionalFee,
            $grandTotal,
            $data['payment_method'],
            $paymentDetails,
            'completed',
            $data['notes'] ?? null
        );
        
        // Save the sales order
        $savedSalesOrder = $this->salesOrderRepository->save($salesOrder);
        
        // Add order details to sales order
        foreach ($orderDetails as $detail) {
            $detail->setSalesOrderId($savedSalesOrder->getId());
            $savedSalesOrder->addOrderDetail($detail);
        }
        
        // Update the sales order with details
        $this->salesOrderRepository->update($savedSalesOrder);
        
        return $this->formatSalesOrderResponse($savedSalesOrder);
    }
    
    public function getSalesOrder(int $id): array
    {
        $salesOrder = $this->salesOrderRepository->findById($id);
        
        if (!$salesOrder) {
            throw new SalesOrderNotFoundException("Sales order not found with ID: {$id}");
        }
        
        return $this->formatSalesOrderResponse($salesOrder);
    }
    
    public function getAllSalesOrders(array $filters = []): array
    {
        $result = $this->salesOrderRepository->getAll($filters);
        
        // Format the sales order data
        $formattedData = $result['data']->map(function ($salesOrder) {
            return $this->formatSalesOrderResponse($salesOrder);
        })->values()->toArray();
        
        // Return the formatted data with pagination
        return [
            'data' => $formattedData,
            'pagination' => $result['pagination']
        ];
    }
    
    public function getPendingSales(): array
    {
        $pendingSales = $this->salesOrderRepository->getPendingSales();
        
        return [
            'data' => $pendingSales->map(function ($sale) {
                return $this->formatSalesOrderResponse($sale);
            })->values()->toArray()
        ];
    }
    
    public function resumeSale(int $saleId): array
    {
        $sale = $this->salesOrderRepository->findById($saleId);
        
        if (!$sale) {
            throw new SalesOrderNotFoundException("Pending sale not found with ID: {$saleId}");
        }
        
        return $this->formatSalesOrderResponse($sale);
    }
    
    public function deletePendingSale(int $saleId): void
    {
        $sale = $this->salesOrderRepository->findById($saleId);
        
        if (!$sale) {
            throw new SalesOrderNotFoundException("Pending sale not found with ID: {$saleId}");
        }
        
        if ($sale->getStatus() !== 'pending') {
            throw new \Exception("Only pending sales can be deleted");
        }
        
        $this->salesOrderRepository->delete($saleId);
    }
    
    public function getRecentSales(int $limit = 10): array
    {
        $recentSales = $this->salesOrderRepository->getRecentSales($limit);
        
        return [
            'data' => $recentSales->map(function ($sale) {
                return $this->formatSalesOrderResponse($sale);
            })->values()->toArray()
        ];
    }
    
    public function getSalesByDateRange(string $startDate, string $endDate): array
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        
        $sales = $this->salesOrderRepository->getSalesByDateRange($start, $end);
        
        return [
            'data' => $sales->map(function ($sale) {
                return $this->formatSalesOrderResponse($sale);
            })->values()->toArray()
        ];
    }
    
    public function getSalesByCustomerId(int $customerId): array
    {
        $sales = $this->salesOrderRepository->getSalesByCustomerId($customerId);
        
        return [
            'data' => $sales->map(function ($sale) {
                return $this->formatSalesOrderResponse($sale);
            })->values()->toArray()
        ];
    }
    
    private function formatSalesOrderResponse(SalesOrder $salesOrder): array
    {
        $details = $salesOrder->getOrderDetails()->map(function ($detail) {
            // Get product information
            // Create a ProductId value object from the primitive integer
            $productId = new \App\Domain\Product\ValueObjects\ProductId($detail->getProductId());
            $product = $this->productRepository->findById($productId);
            
            return [
                'id' => $detail->getId(),
                'product_id' => $detail->getProductId(),
                'quantity' => $detail->getQuantity(),
                'price' => $detail->getPrice(),
                'subtotal' => $detail->getSubtotal(),
                'product' => $product ? [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'unit' => $product->getUnit(),
                    'price' => $product->getPrice()
                ] : null
            ];
        })->values()->toArray();
        
        return [
            'id' => $salesOrder->getId(),
            'invoice_number' => $salesOrder->getInvoiceNumber(),
            'customer_id' => $salesOrder->getCustomerId(),
            'customer_name' => $salesOrder->getCustomerName(),
            'customer_phone' => $salesOrder->getCustomerPhone(),
            'total' => $salesOrder->getTotal(),
            'discount' => $salesOrder->getDiscount(),
            'additional_fee' => $salesOrder->getAdditionalFee(),
            'grand_total' => $salesOrder->getGrandTotal(),
            'payment_method' => $salesOrder->getPaymentMethod(),
            'payment_details' => $salesOrder->getPaymentDetails()->toArray(),
            'status' => $salesOrder->getStatus(),
            'notes' => $salesOrder->getNotes(),
            'created_at' => $salesOrder->getCreatedAt() ? $salesOrder->getCreatedAt()->format('Y-m-d H:i:s') : null,
            'updated_at' => $salesOrder->getUpdatedAt() ? $salesOrder->getUpdatedAt()->format('Y-m-d H:i:s') : null,
            'details' => $details
        ];
    }
}
