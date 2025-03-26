<?php

namespace App\Application\Services;

use App\Application\Contracts\Customer\CustomerManagementServiceInterface;
use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Services\CustomerService;
use App\Domain\Customer\ValueObjects\CustomerId;

class CustomerManagementService implements CustomerManagementServiceInterface
{
    public function __construct(
        private readonly CustomerService $customerService
    ) {}

    /**
     * Get all customers with optional filtering
     */
    public function getAllCustomers(array $filters = []): array
    {
        $customers = $this->customerService->findBySearchCriteria($filters);
        
        // Convert domain objects to array representation
        return array_map(function (Customer $customer) {
            return $customer->toArray();
        }, $customers);
    }
    
    /**
     * Get a customer by ID
     */
    public function getCustomerById(int $id): Customer
    {
        return $this->customerService->findById($id);
    }
    
    /**
     * Create a new customer
     */
    public function createCustomer(array $data): Customer
    {
        return $this->customerService->createCustomer(
            $data['name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['address'] ?? null
        );
    }
    
    /**
     * Update an existing customer
     */
    public function updateCustomer(int $id, array $data): Customer
    {
        return $this->customerService->updateCustomer(
            $id,
            $data['name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['address'] ?? null
        );
    }
    
    /**
     * Delete a customer
     */
    public function deleteCustomer(int $id): bool
    {
        return $this->customerService->deleteCustomer($id);
    }
    
    /**
     * Search for customers by query
     */
    public function searchCustomers(string $query): array
    {
        $criteria = ['query' => $query];
        $customers = $this->customerService->findBySearchCriteria($criteria);
        
        // Convert domain objects to array representation
        return array_map(function (Customer $customer) {
            return $customer->toArray();
        }, $customers);
    }
}
