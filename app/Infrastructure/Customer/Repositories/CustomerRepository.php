<?php

namespace App\Infrastructure\Customer\Repositories;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Repositories\CustomerRepositoryInterface;
use App\Domain\Customer\ValueObjects\CustomerId;
use App\Infrastructure\Customer\Mappers\CustomerMapper;
use App\Models\Customer as CustomerEloquent;
use Illuminate\Support\Facades\DB;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(
        private readonly CustomerMapper $mapper
    ) {}

    /**
     * Find a customer by ID
     */
    public function findById(CustomerId $id): ?Customer
    {
        $model = CustomerEloquent::find($id->getValue());
        
        if (!$model) {
            return null;
        }
        
        return $this->mapper->toDomain($model);
    }
    
    /**
     * Find customers by search criteria
     */
    public function findBySearchCriteria(array $criteria): array
    {
        $query = CustomerEloquent::query();
        
        // Apply search query if provided
        if (isset($criteria['query'])) {
            $searchQuery = $criteria['query'];
            $query->where(function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('phone', 'like', "%{$searchQuery}%")
                  ->orWhere('email', 'like', "%{$searchQuery}%");
            });
        }
        
        // Apply date filters if provided
        if (isset($criteria['joinDateStart'])) {
            $query->whereDate('created_at', '>=', $criteria['joinDateStart']);
        }
        
        if (isset($criteria['joinDateEnd'])) {
            $query->whereDate('created_at', '<=', $criteria['joinDateEnd']);
        }
        
        // Get results and map to domain models
        $customers = $query->latest()->get();
        
        return $customers->map(function($customer) {
            return $this->mapper->toDomain($customer);
        })->all();
    }
    
    /**
     * Save a customer (create or update)
     */
    public function save(Customer $customer): Customer
    {
        $data = $this->mapper->toPersistence($customer);
        
        if ($customer->getId()->getValue() === 0) {
            // Create new record
            $model = CustomerEloquent::create($data);
        } else {
            // Update existing record
            $model = CustomerEloquent::find($customer->getId()->getValue());
            if (!$model) {
                throw new \RuntimeException("Customer not found for update");
            }
            $model->update($data);
        }
        
        return $this->mapper->toDomain($model);
    }
    
    /**
     * Delete a customer
     */
    public function delete(CustomerId $id): bool
    {
        $model = CustomerEloquent::find($id->getValue());
        
        if (!$model) {
            return false;
        }
        
        return (bool) $model->delete();
    }
    
    /**
     * Check if a customer has transactions
     */
    public function hasTransactions(CustomerId $id): bool
    {
        $model = CustomerEloquent::find($id->getValue());
        
        if (!$model) {
            return false;
        }
        
        return $model->transactions()->exists();
    }
}
