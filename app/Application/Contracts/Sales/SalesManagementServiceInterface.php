<?php

namespace App\Application\Contracts\Sales;

use App\Domain\Sales\Models\SalesOrder;
use Illuminate\Support\Collection;

interface SalesManagementServiceInterface
{
    /**
     * Create a new sales order
     */
    public function createSalesOrder(array $data): array;
    
    /**
     * Get a specific sales order by ID
     */
    public function getSalesOrder(int $id): array;
    
    /**
     * Get all sales orders with optional filtering
     */
    public function getAllSalesOrders(array $filters = []): array;
    
    /**
     * Get pending sales
     */
    public function getPendingSales(): array;
    
    /**
     * Resume a pending sale
     */
    public function resumeSale(int $saleId): array;
    
    /**
     * Delete a pending sale
     */
    public function deletePendingSale(int $saleId): void;
    
    /**
     * Get recent sales
     */
    public function getRecentSales(int $limit = 10): array;
    
    /**
     * Get sales by date range
     */
    public function getSalesByDateRange(string $startDate, string $endDate): array;
    
    /**
     * Get sales by customer ID
     */
    public function getSalesByCustomerId(int $customerId): array;
}
