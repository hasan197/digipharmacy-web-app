<?php

namespace App\Application\Contracts\Customer;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\ValueObjects\CustomerId;

interface CustomerManagementServiceInterface
{
    /**
     * Get all customers with optional filtering
     */
    public function getAllCustomers(array $filters = []): array;
    
    /**
     * Get a customer by ID
     */
    public function getCustomerById(int $id): Customer;
    
    /**
     * Create a new customer
     */
    public function createCustomer(array $data): Customer;
    
    /**
     * Update an existing customer
     */
    public function updateCustomer(int $id, array $data): Customer;
    
    /**
     * Delete a customer
     */
    public function deleteCustomer(int $id): bool;
    
    /**
     * Search for customers by query
     */
    public function searchCustomers(string $query): array;
}
