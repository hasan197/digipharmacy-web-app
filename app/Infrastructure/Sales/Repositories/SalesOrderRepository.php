<?php

namespace App\Infrastructure\Sales\Repositories;

use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Repositories\SalesOrderRepositoryInterface;
use App\Infrastructure\Sales\Mappers\SalesOrderMapper;
use App\Models\SalesOrder as EloquentSalesOrder;
use Illuminate\Support\Collection;

class SalesOrderRepository implements SalesOrderRepositoryInterface
{
    public function __construct(
        private SalesOrderMapper $mapper
    ) {}

    public function findById(int $id): ?SalesOrder
    {
        $salesOrder = EloquentSalesOrder::with('details.product', 'customer')->find($id);
        return $salesOrder ? $this->mapper->toDomain($salesOrder) : null;
    }

    public function findByInvoiceNumber(string $invoiceNumber): ?SalesOrder
    {
        $salesOrder = EloquentSalesOrder::with('details.product', 'customer')
            ->where('invoice_number', $invoiceNumber)
            ->first();
        return $salesOrder ? $this->mapper->toDomain($salesOrder) : null;
    }

    public function save(SalesOrder $salesOrder): SalesOrder
    {
        $eloquentSalesOrder = $this->mapper->toEloquent($salesOrder);
        $eloquentSalesOrder->save();
        
        // Reload the model with relationships
        $eloquentSalesOrder = EloquentSalesOrder::with('details.product', 'customer')->find($eloquentSalesOrder->id);
        
        return $this->mapper->toDomain($eloquentSalesOrder);
    }

    public function update(SalesOrder $salesOrder): SalesOrder
    {
        $eloquentSalesOrder = $this->mapper->toEloquent($salesOrder);
        $eloquentSalesOrder->save();
        
        // Reload the model with relationships
        $eloquentSalesOrder = EloquentSalesOrder::with('details.product', 'customer')->find($eloquentSalesOrder->id);
        
        return $this->mapper->toDomain($eloquentSalesOrder);
    }

    public function delete(int $salesOrderId): void
    {
        EloquentSalesOrder::destroy($salesOrderId);
    }

    public function getAll(array $filters = []): array
    {
        $query = EloquentSalesOrder::with('details.product', 'customer');
        
        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }
        
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }
        
        // Get pagination parameters
        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;
        
        // Get paginated results
        $paginatedSales = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
        
        // Map the sales orders to domain models
        $salesOrders = collect($paginatedSales->items())->map(function ($salesOrder) {
            return $this->mapper->toDomain($salesOrder);
        });
        
        // Return both the collection and pagination data
        return [
            'data' => $salesOrders,
            'pagination' => [
                'current_page' => $paginatedSales->currentPage(),
                'last_page' => $paginatedSales->lastPage(),
                'per_page' => $paginatedSales->perPage(),
                'total' => $paginatedSales->total()
            ]
        ];
    }

    public function getPendingSales(): Collection
    {
        $pendingSales = EloquentSalesOrder::with('details.product', 'customer')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return $pendingSales->map(function ($sale) {
            return $this->mapper->toDomain($sale);
        });
    }

    public function getRecentSales(int $limit = 10): Collection
    {
        $recentSales = EloquentSalesOrder::with('details.product', 'customer')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        
        return $recentSales->map(function ($sale) {
            return $this->mapper->toDomain($sale);
        });
    }

    public function getSalesByDateRange(\DateTime $startDate, \DateTime $endDate): Collection
    {
        $sales = EloquentSalesOrder::with('details.product', 'customer')
            ->whereBetween('created_at', [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return $sales->map(function ($sale) {
            return $this->mapper->toDomain($sale);
        });
    }

    public function getSalesByCustomerId(int $customerId): Collection
    {
        $sales = EloquentSalesOrder::with('details.product', 'customer')
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return $sales->map(function ($sale) {
            return $this->mapper->toDomain($sale);
        });
    }
}
