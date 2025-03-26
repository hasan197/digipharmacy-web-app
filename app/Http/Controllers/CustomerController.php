<?php

namespace App\Http\Controllers;

use App\Services\LogService;
use App\Application\Contracts\Customer\CustomerManagementServiceInterface;
use App\Domain\Customer\Exceptions\CustomerNotFoundException;
use App\Domain\Customer\Exceptions\CustomerHasTransactionsException;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    private CustomerManagementServiceInterface $customerService;
    
    public function __construct(CustomerManagementServiceInterface $customerService)
    {
        $this->customerService = $customerService;
    }
    
    public function index(Request $request)
    {
        // Get customers using the service with pagination
        $page = $request->query('page', 1);
        $perPage = 10;
        $filters = [];
        
        // Get total count for pagination
        $allCustomers = $this->customerService->getAllCustomers($filters);
        $total = count($allCustomers);
        
        // Apply pagination manually
        $offset = ($page - 1) * $perPage;
        $paginatedCustomers = array_slice($allCustomers, $offset, $perPage);
        
        // Create a custom paginator
        $customers = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedCustomers,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string'
        ]);

        try {
            // Use the DDD service to create a customer
            $customer = $this->customerService->createCustomer($validated);
            
            LogService::pos('New customer registered', [
                'customer_id' => $customer->getId()->getValue(),
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
                'created_by' => auth()->id()
            ]);
        } catch (Exception $e) {
            LogService::error('Failed to create customer', [
                'error' => $e->getMessage(),
                'data' => $validated,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function edit($id)
    {
        try {
            $customer = $this->customerService->getCustomerById($id);
            return view('customers.edit', compact('customer'));
        } catch (CustomerNotFoundException $e) {
            return redirect()->route('customers.index')
                ->with('error', 'Customer not found.');
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string'
        ]);

        try {
            // Use the DDD service to update a customer
            $customer = $this->customerService->updateCustomer($id, $validated);
            
            LogService::pos('Customer information updated', [
                'customer_id' => $customer->getId()->getValue(),
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
                'updated_by' => auth()->id()
            ]);
        } catch (CustomerNotFoundException $e) {
            return redirect()->route('customers.index')
                ->with('error', 'Customer not found.');
        } catch (Exception $e) {
            LogService::error('Failed to update customer', [
                'error' => $e->getMessage(),
                'customer_id' => $id,
                'data' => $validated,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy($id)
    {
        try {
            // Use the DDD service to delete a customer
            $this->customerService->deleteCustomer($id);
            
            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully.');
        } catch (CustomerNotFoundException $e) {
            return redirect()->route('customers.index')
                ->with('error', 'Customer not found.');
        } catch (CustomerHasTransactionsException $e) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with transaction history.');
        } catch (Exception $e) {
            LogService::error('Failed to delete customer', [
                'error' => $e->getMessage(),
                'customer_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('customers.index')
                ->with('error', 'An error occurred while deleting the customer.');
        }
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        if (!$query) {
            return response()->json([]);
        }

        try {
            // Use the DDD service to search for customers
            $customers = $this->customerService->searchCustomers($query);
            
            // Limit to 10 results
            $limitedResults = array_slice($customers, 0, 10);

            return response()->json($limitedResults);
        } catch (Exception $e) {
            LogService::error('Failed to search customers', [
                'error' => $e->getMessage(),
                'query' => $query,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to search customers'], 500);
        }
    }

    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|unique:customers,email',
            'address' => 'nullable|string'
        ]);

        try {
            // Use the DDD service to create a customer
            $customer = $this->customerService->createCustomer($validated);
            
            LogService::pos('New customer registered via API', [
                'customer_id' => $customer->getId()->getValue(),
                'name' => $customer->getName(),
                'phone' => $customer->getPhone(),
                'created_by' => auth()->id()
            ]);

            return response()->json($customer->toArray(), 201);
        } catch (Exception $e) {
            LogService::error('Failed to create customer via API', [
                'error' => $e->getMessage(),
                'data' => $validated,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return more specific error message
            $errorMessage = 'Failed to create customer';
            if ($e->getMessage()) {
                $errorMessage .= ': ' . $e->getMessage();
            }
            
            return response()->json(['error' => $errorMessage], 500);
        }
    }

    /**
     * API method to get all customers
     */
    public function apiIndex(Request $request)
    {
        try {
            $filters = [];
            
            // Apply filters if provided
            if ($request->has('query')) {
                $filters['query'] = $request->query('query');
            }
            
            if ($request->has('joinDateStart')) {
                $filters['joinDateStart'] = $request->query('joinDateStart');
            }
            
            if ($request->has('joinDateEnd')) {
                $filters['joinDateEnd'] = $request->query('joinDateEnd');
            }
            
            // Use the DDD service to get customers
            $customers = $this->customerService->getAllCustomers($filters);
            
            return response()->json($customers);
        } catch (Exception $e) {
            LogService::error('Failed to fetch customers via API', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to fetch customers'], 500);
        }
    }

    /**
     * API method to get a single customer
     */
    public function apiShow($id)
    {
        try {
            // Use the DDD service to get a customer by ID
            $customer = $this->customerService->getCustomerById($id);
            
            return response()->json($customer->toArray());
        } catch (CustomerNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (Exception $e) {
            LogService::error('Failed to fetch customer via API', [
                'error' => $e->getMessage(),
                'customer_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to fetch customer'], 500);
        }
    }

    /**
     * API method to update a customer
     */
    public function apiUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|unique:customers,email,' . $id,
            'address' => 'nullable|string'
        ]);

        try {
            // Use the DDD service to update a customer
            $customer = $this->customerService->updateCustomer($id, $validated);
            
            LogService::pos('Customer updated via API', [
                'customer_id' => $id,
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'updated_by' => auth()->id()
            ]);
            
            return response()->json($customer->toArray());
        } catch (CustomerNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (Exception $e) {
            LogService::error('Failed to update customer via API', [
                'error' => $e->getMessage(),
                'customer_id' => $id,
                'data' => $validated,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to update customer'], 500);
        }
    }

    /**
     * API method to delete a customer
     */
    public function apiDestroy($id)
    {
        try {
            // Use the DDD service to delete a customer
            $result = $this->customerService->deleteCustomer($id);
            
            LogService::pos('Customer deleted via API', [
                'customer_id' => $id,
                'deleted_by' => auth()->id()
            ]);
            
            return response()->json(['message' => 'Customer deleted successfully']);
        } catch (CustomerNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (CustomerHasTransactionsException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (Exception $e) {
            LogService::error('Failed to delete customer via API', [
                'error' => $e->getMessage(),
                'customer_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to delete customer'], 500);
        }
    }
}
