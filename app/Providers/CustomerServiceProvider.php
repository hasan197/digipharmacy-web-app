<?php

namespace App\Providers;

use App\Application\Contracts\Customer\CustomerManagementServiceInterface;
use App\Application\Services\CustomerManagementService;
use App\Domain\Customer\Repositories\CustomerRepositoryInterface;
use App\Domain\Customer\Services\CustomerService;
use App\Infrastructure\Customer\Repositories\CustomerRepository;
use App\Infrastructure\Customer\Mappers\CustomerMapper;
use Illuminate\Support\ServiceProvider;

class CustomerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register mappers
        $this->app->bind(CustomerMapper::class, function ($app) {
            return new CustomerMapper();
        });
        
        // Register repositories
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        
        // Register domain services
        $this->app->bind(CustomerService::class, function ($app) {
            return new CustomerService(
                $app->make(CustomerRepositoryInterface::class)
            );
        });
        
        // Register application services
        $this->app->bind(CustomerManagementServiceInterface::class, function ($app) {
            return new CustomerManagementService(
                $app->make(CustomerService::class)
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
