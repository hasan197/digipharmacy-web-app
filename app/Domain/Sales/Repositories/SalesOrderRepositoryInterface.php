<?php

namespace App\Domain\Sales\Repositories;

use App\Domain\Sales\Models\SalesOrder;
use Illuminate\Support\Collection;

interface SalesOrderRepositoryInterface
{
    public function findById(int $id): ?SalesOrder;
    public function findByInvoiceNumber(string $invoiceNumber): ?SalesOrder;
    public function save(SalesOrder $salesOrder): SalesOrder;
    public function update(SalesOrder $salesOrder): SalesOrder;
    public function delete(int $salesOrderId): void;
    public function getAll(array $filters = []): array;
    public function getPendingSales(): Collection;
    public function getRecentSales(int $limit = 10): Collection;
    public function getSalesByDateRange(\DateTime $startDate, \DateTime $endDate): Collection;
    public function getSalesByCustomerId(int $customerId): Collection;
}
