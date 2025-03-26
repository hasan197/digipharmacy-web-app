<?php

namespace App\Domain\Customer\Repositories;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\ValueObjects\CustomerId;

interface CustomerRepositoryInterface
{
    /**
     * Find a customer by ID
     */
    public function findById(CustomerId $id): ?Customer;
    
    /**
     * Find customers by search criteria
     */
    public function findBySearchCriteria(array $criteria): array;
    
    /**
     * Save a customer (create or update)
     */
    public function save(Customer $customer): Customer;
    
    /**
     * Delete a customer
     */
    public function delete(CustomerId $id): bool;
    
    /**
     * Check if a customer has transactions
     */
    public function hasTransactions(CustomerId $id): bool;
}
