<?php

namespace App\Domain\Customer\Services;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Repositories\CustomerRepositoryInterface;
use App\Domain\Customer\ValueObjects\CustomerId;
use App\Domain\Customer\Exceptions\CustomerNotFoundException;
use App\Domain\Customer\Exceptions\CustomerHasTransactionsException;
use DateTime;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository
    ) {}

    /**
     * Find a customer by ID
     */
    public function findById(int $id): Customer
    {
        $customerId = new CustomerId($id);
        $customer = $this->customerRepository->findById($customerId);
        
        if (!$customer) {
            throw new CustomerNotFoundException($id);
        }
        
        return $customer;
    }
    
    /**
     * Create a new customer
     */
    public function createCustomer(
        string $name,
        string $phone,
        ?string $email = null,
        ?string $address = null
    ): Customer {
        // Create a temporary ID that will be replaced by the repository
        $tempId = new CustomerId(0);
        $customer = new Customer($tempId, $name, $phone, $email, $address, new DateTime());
        
        return $this->customerRepository->save($customer);
    }
    
    /**
     * Update a customer's information
     */
    public function updateCustomer(
        int $id,
        string $name,
        string $phone,
        ?string $email = null,
        ?string $address = null
    ): Customer {
        $customer = $this->findById($id);
        $customer->updateInformation($name, $phone, $email, $address);
        
        return $this->customerRepository->save($customer);
    }
    
    /**
     * Delete a customer
     */
    public function deleteCustomer(int $id): bool
    {
        $customerId = new CustomerId($id);
        
        // Check if customer has transactions
        if ($this->customerRepository->hasTransactions($customerId)) {
            throw new CustomerHasTransactionsException($id);
        }
        
        // Check if customer exists
        if (!$this->customerRepository->findById($customerId)) {
            throw new CustomerNotFoundException($id);
        }
        
        return $this->customerRepository->delete($customerId);
    }
    
    /**
     * Find customers by search criteria
     */
    public function findBySearchCriteria(array $criteria): array
    {
        return $this->customerRepository->findBySearchCriteria($criteria);
    }
}
