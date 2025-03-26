<?php

namespace App\Http\Controllers;

use App\Application\Contracts\Inventory\InventoryManagementServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryManagementServiceInterface $inventoryService
    ) {}

    /**
     * Get latest inventory transactions
     */
    public function getLatestTransactions(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $result = $this->inventoryService->getLatestTransactions($limit);
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json($result['data']);
    }
    
    /**
     * Get transaction by ID
     */
    public function getTransactionById(int $id): JsonResponse
    {
        $result = $this->inventoryService->getTransactionById($id);
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 404);
        }
        
        return response()->json($result['data']);
    }
    
    /**
     * Get product transaction history
     */
    public function getProductTransactionHistory(int $productId): JsonResponse
    {
        $result = $this->inventoryService->getProductTransactionHistory($productId);
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json($result['data']);
    }
    
    /**
     * Get transactions by date range
     */
    public function getTransactionsByDateRange(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $result = $this->inventoryService->getTransactionsByDateRange($startDate, $endDate);
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json($result['data']);
    }
    
    /**
     * Get products with low stock
     */
    public function getLowStockProducts(Request $request): JsonResponse
    {
        $threshold = $request->input('threshold', 10);
        $result = $this->inventoryService->getLowStockProducts($threshold);
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json($result['data']);
    }
    
    /**
     * Record stock in
     */
    public function recordStockIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
            'reference_id' => 'nullable|integer',
            'reference_type' => 'nullable|string|max:100'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->inventoryService->recordStockIn(
            $request->input('product_id'),
            $request->input('quantity'),
            $request->input('notes'),
            $request->input('reference_id'),
            $request->input('reference_type')
        );
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json([
            'message' => $result['message'],
            'data' => $result['data']
        ], 201);
    }
    
    /**
     * Record stock out
     */
    public function recordStockOut(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
            'reference_id' => 'nullable|integer',
            'reference_type' => 'nullable|string|max:100'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->inventoryService->recordStockOut(
            $request->input('product_id'),
            $request->input('quantity'),
            $request->input('notes'),
            $request->input('reference_id'),
            $request->input('reference_type')
        );
        
        if (!$result['success']) {
            if (isset($result['product_id'])) {
                // Insufficient stock error
                return response()->json([
                    'message' => $result['message'],
                    'product_id' => $result['product_id'],
                    'requested_quantity' => $result['requested_quantity'],
                    'available_quantity' => $result['available_quantity']
                ], 422);
            }
            
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json([
            'message' => $result['message'],
            'data' => $result['data']
        ], 201);
    }
    
    /**
     * Adjust stock
     */
    public function adjustStock(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'new_stock_level' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->inventoryService->adjustStock(
            $request->input('product_id'),
            $request->input('new_stock_level'),
            $request->input('notes')
        );
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json([
            'message' => $result['message'],
            'data' => $result['data']
        ], 200);
    }

    /**
     * Get all products with stock information
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('search', '');
        $categoryId = $request->query('category_id');
        
        if ($categoryId !== null) {
            $categoryId = (int) $categoryId;
        }
        
        $result = $this->inventoryService->getAllProductsWithStock($query, $categoryId);
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json($result);
    }
} 