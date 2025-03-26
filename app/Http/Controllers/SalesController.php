<?php

namespace App\Http\Controllers;

use App\Application\Contracts\Sales\SalesManagementServiceInterface;
use App\Domain\Sales\Exceptions\InsufficientStockException;
use App\Domain\Sales\Exceptions\SalesOrderNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Exception;

class SalesController extends Controller
{
    public function __construct(
        private readonly SalesManagementServiceInterface $salesService
    ) {}

    /**
     * Handle domain exceptions and other errors
     */
    protected function handleException(\Exception $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        if ($e instanceof InsufficientStockException) {
            return response()->json([
                'message' => 'Insufficient stock',
                'error' => $e->getMessage()
            ], 400);
        }

        if ($e instanceof SalesOrderNotFoundException) {
            return response()->json([
                'message' => 'Not found',
                'error' => $e->getMessage()
            ], 404);
        }

        // Log unexpected errors
        \Log::error('Sales management error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'message' => 'An unexpected error occurred',
            'error' => $e->getMessage()
        ], 500);
    }

    public function store(Request $request): JsonResponse
    {
        try {

            $validated = $request->validate([
                'customer_id' => 'nullable|exists:customers,id',
                'customer_name' => 'nullable|string|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'payment_method' => 'required|in:cash,debit,credit,qris',
                'payment_details' => 'required|array',
                'payment_details.method' => 'required|string',
                'payment_details.isValid' => 'required',
                'payment_details.cardLast4' => 'nullable|string',
                'payment_details.cardType' => 'nullable|string',
                'payment_details.approvalCode' => 'nullable|string',
                'payment_details.transactionId' => 'nullable|string',
                'payment_details.amountPaid' => 'nullable|numeric',
                'payment_details.change' => 'nullable|numeric',
                'notes' => 'nullable|string',
            ]);

            $result = $this->salesService->createSalesOrder($validated);

            return response()->json([
                'message' => 'Sale created successfully',
                'sale' => $result
            ], 201);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [];
            
            if ($request->has('status')) {
                $filters['status'] = $request->status;
            }
            
            if ($request->has('payment_method')) {
                $filters['payment_method'] = $request->payment_method;
            }
            
            if ($request->has('start_date') && $request->has('end_date')) {
                $filters['start_date'] = $request->start_date;
                $filters['end_date'] = $request->end_date;
            }
            
            // Add pagination parameters
            $filters['page'] = $request->input('page', 1);
            $filters['per_page'] = $request->input('per_page', 10);
            
            $result = $this->salesService->getAllSalesOrders($filters);
            
            return response()->json($result);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->salesService->getSalesOrder($id);
            
            return response()->json($result);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getPendingSales(): JsonResponse
    {
        try {
            $result = $this->salesService->getPendingSales();
            
            return response()->json($result);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function resumeSale(int $saleId): JsonResponse
    {
        try {
            $result = $this->salesService->resumeSale($saleId);
            
            return response()->json([
                'message' => 'Sale resumed successfully',
                'sale' => $result
            ]);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function deletePendingSale(int $saleId): JsonResponse
    {
        try {
            $this->salesService->deletePendingSale($saleId);
            
            return response()->json([
                'message' => 'Pending sale deleted successfully'
            ]);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getRecentSales(Request $request): JsonResponse
    {
        try {
            $limit = $request->limit ?? 10;
            $result = $this->salesService->getRecentSales($limit);
            
            return response()->json($result);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getSalesByDateRange(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);
            
            $result = $this->salesService->getSalesByDateRange(
                $request->start_date,
                $request->end_date
            );
            
            return response()->json($result);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getSalesByCustomerId(int $customerId): JsonResponse
    {
        try {
            $result = $this->salesService->getSalesByCustomerId($customerId);
            
            return response()->json($result);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
