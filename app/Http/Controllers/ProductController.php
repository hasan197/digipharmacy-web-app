<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Services\LogService;
use Illuminate\Http\Request;
use Exception;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $products = Product::with('category')->latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'expiry_date' => 'required|date',
            'requires_prescription' => 'boolean'
        ]);

        try {
            $product = Product::create($validated);

            LogService::pos('New product added to inventory', [
                'product_id' => $product->id,
                'name' => $product->name,
                'category' => $product->category_id,
                'stock' => $product->stock,
                'requires_prescription' => $product->requires_prescription,
                'user_id' => auth()->id()
            ]);
        } catch (Exception $e) {
            LogService::error('Failed to add product', [
                'error' => $e->getMessage(),
                'data' => $validated,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'expiry_date' => 'required|date',
            'requires_prescription' => 'boolean'
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function getProducts()
    {
        $products = Product::with('category')->get();
        return response()->json($products);
    }

    public function searchProducts(Request $request)
    {
        $query = $request->get('query', '');
        
        $products = Product::with('category')
            ->where('name', 'like', "%{$query}%")
            ->orWhereHas('category', function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->get();
        
        return response()->json($products);
    }
}
