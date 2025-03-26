<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Application\Contracts\Sales\SalesManagementServiceInterface;
use App\Domain\Sales\Services\SalesManagementService;
use App\Domain\Sales\Repositories\SalesOrderRepositoryInterface;
use App\Infrastructure\Sales\Repositories\SalesOrderRepository;
use App\Infrastructure\Sales\Mappers\SalesOrderMapper;

class SalesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register mappers
        $this->app->singleton(SalesOrderMapper::class, function ($app) {
            return new SalesOrderMapper();
        });

        // Register repositories
        $this->app->singleton(SalesOrderRepositoryInterface::class, function ($app) {
            return new SalesOrderRepository(
                $app->make(SalesOrderMapper::class)
            );
        });

        // Register services
        $this->app->singleton(SalesManagementServiceInterface::class, function ($app) {
            return new SalesManagementService(
                $app->make(SalesOrderRepositoryInterface::class),
                $app->make(\App\Domain\Product\Repositories\ProductRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
