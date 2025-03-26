<?php

namespace App\Http\Controllers;

use App\Application\Contracts\Product\ProductManagementServiceInterface;
use App\Domain\Product\Exceptions\ProductNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProductManagementController extends Controller
{
    public function __construct(
        private readonly ProductManagementServiceInterface $productService
    ) {}

    /**
     * Get all products with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [];
        
        if ($request->has('query')) {
            $filters['query'] = $request->input('query');
        }
        
        if ($request->has('category_id')) {
            $filters['category_id'] = $request->input('category_id');
        }
        
        if ($request->has('status')) {
            $filters['status'] = $request->input('status');
        }
        
        if ($request->has('min_price')) {
            $filters['min_price'] = $request->input('min_price');
        }
        
        if ($request->has('max_price')) {
            $filters['max_price'] = $request->input('max_price');
        }
        
        if ($request->has('requires_prescription')) {
            $filters['requires_prescription'] = $request->boolean('requires_prescription');
        }
        
        if ($request->has('sort_by')) {
            $filters['sort_by'] = $request->input('sort_by');
            $filters['sort_direction'] = $request->input('sort_direction', 'asc');
        }
        
        $result = $this->productService->getAllProducts($filters);
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json($result);
    }

    /**
     * Get a specific product by ID
     */
    public function show(int $id): JsonResponse
    {
        $result = $this->productService->getProductById($id);
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 404);
        }
        
        return response()->json($result);
    }

    /**
     * Create a new product
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:50',
            'expiry_date' => 'nullable|date',
            'requires_prescription' => 'nullable|boolean',
            'status' => 'nullable|string|in:active,inactive,discontinued',
            'sku' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:50'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->productService->createProduct($request->all());
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json($result, 201);
    }

    /**
     * Update an existing product
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'unit' => 'required|string|max:50',
            'expiry_date' => 'nullable|date',
            'requires_prescription' => 'nullable|boolean',
            'status' => 'nullable|string|in:active,inactive,discontinued',
            'sku' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:50'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->productService->updateProduct($id, $request->all());
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['message'] === "Product with ID {$id} not found" ? 404 : 500);
        }
        
        return response()->json($result);
    }

    /**
     * Delete a product
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->productService->deleteProduct($id);
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['message'] === "Product with ID {$id} not found" ? 404 : 500);
        }
        
        return response()->json($result);
    }

    /**
     * Change product status
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:active,inactive,discontinued'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->productService->changeProductStatus($id, $request->input('status'));
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['message'] === "Product with ID {$id} not found" ? 404 : 500);
        }
        
        return response()->json($result);
    }

    /**
     * Get products by category
     */
    public function getByCategory(int $categoryId): JsonResponse
    {
        $result = $this->productService->getProductsByCategory($categoryId);
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json($result);
    }

    /**
     * Get products with low stock
     */
    public function getLowStock(Request $request): JsonResponse
    {
        $threshold = $request->input('threshold', 10);
        $result = $this->productService->getLowStockProducts($threshold);
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json($result);
    }

    /**
     * Search products
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query', '');
        $result = $this->productService->searchProducts($query);
        
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        
        return response()->json($result);
    }
} 