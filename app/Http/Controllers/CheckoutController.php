<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\LogService;
use Illuminate\Http\Request;
use Exception;

class CheckoutController extends Controller
{
    /**
     * Handle the checkout process.
     *
     * @param Request $request
     * @return \\Illuminate\Http\JsonResponse
     *
     * Validates the incoming request data and creates a new sale record in the database.
     * Required fields:
     * - sale_number: Unique identifier for the sale.
     * - customer_id: ID of the customer making the purchase.
     * - status: Current status of the sale (e.g., completed).
     * - amount: Total amount of the sale.
     * - item_count: Total number of items in the sale.
     * - payment_type: Method of payment used for the sale.
     */
    public function checkout(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'sale_number' => 'required|string',
            'customer_id' => 'required|integer',
            'status' => 'required|string',
            'amount' => 'required|numeric',
            'item_count' => 'required|integer',
            'payment_type' => 'required|string',
        ]);

        try {
            // Create a new sale record
            $sale = Sale::create($validatedData);

            // Log successful checkout
            LogService::pos('Checkout successful', [
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'amount' => $sale->amount,
                'payment_type' => $sale->payment_type,
                'item_count' => $sale->item_count,
                'user_id' => auth()->id()
            ]);

            return response()->json(['message' => 'Checkout successful', 'sale' => $sale], 201);

        } catch (Exception $e) {
            // Log checkout error
            LogService::error('Checkout failed', [
                'error' => $e->getMessage(),
                'sale_data' => $validatedData,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['message' => 'Checkout failed', 'error' => $e->getMessage()], 500);
        }
    }
}