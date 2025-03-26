<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Auth\Services\PermissionValidationService;
use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Repositories\ProductRepositoryInterface;

class InventoryService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function getProducts(): array
    {
        // Validasi permission untuk melihat inventory
        PermissionValidationService::validatePermission('inventory', 'view');

        return $this->productRepository->getAllProducts();
    }

    public function createProduct(array $data): Product
    {
        // Validasi permission untuk membuat produk baru
        PermissionValidationService::validatePermission('inventory', 'create');

        return $this->productRepository->createProduct($data);
    }

    public function updateProduct(int $id, array $data): Product
    {
        // Validasi permission untuk mengupdate produk
        PermissionValidationService::validatePermission('inventory', 'update');

        return $this->productRepository->updateProduct($id, $data);
    }

    public function deleteProduct(int $id): void
    {
        // Validasi permission untuk menghapus produk
        PermissionValidationService::validatePermission('inventory', 'delete');

        $this->productRepository->deleteProduct($id);
    }
}
