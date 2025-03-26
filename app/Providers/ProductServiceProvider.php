<?php

namespace App\Providers;

use App\Application\Contracts\Product\ProductManagementServiceInterface;
use App\Application\Services\ProductManagementService;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\Services\ProductService;
use App\Infrastructure\Product\Mappers\ProductMapper;
use App\Infrastructure\Product\Repositories\ProductRepository;
use Illuminate\Support\ServiceProvider;

class ProductServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind mappers
        $this->app->bind(ProductMapper::class, function ($app) {
            return new ProductMapper();
        });
        
        // Bind repositories
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        
        // Bind domain services
        $this->app->bind(ProductService::class, function ($app) {
            return new ProductService(
                $app->make(ProductRepositoryInterface::class)
            );
        });
        
        // Bind application services
        $this->app->bind(ProductManagementServiceInterface::class, ProductManagementService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
