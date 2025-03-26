<?php

namespace App\Providers;

use App\Application\Contracts\Inventory\InventoryManagementServiceInterface;
use App\Application\Services\InventoryManagementService as ApplicationInventoryService;
use App\Domain\Inventory\Repositories\InventoryTransactionRepositoryInterface;
use App\Domain\Inventory\Services\InventoryManagementService as DomainInventoryService;
use App\Infrastructure\Inventory\Mappers\InventoryTransactionMapper;
use App\Infrastructure\Inventory\Repositories\InventoryTransactionRepository;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind mappers
        $this->app->bind(InventoryTransactionMapper::class, function ($app) {
            return new InventoryTransactionMapper();
        });
        
        // Bind repositories
        $this->app->bind(InventoryTransactionRepositoryInterface::class, InventoryTransactionRepository::class);
        
        // Bind domain services
        $this->app->bind(DomainInventoryService::class, function ($app) {
            return new DomainInventoryService(
                $app->make(\App\Domain\Product\Repositories\ProductRepositoryInterface::class),
                $app->make(InventoryTransactionRepositoryInterface::class)
            );
        });
        
        // Bind application services
        $this->app->bind(InventoryManagementServiceInterface::class, ApplicationInventoryService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 