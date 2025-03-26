<?php

namespace App\Observers;

use App\Models\SalesOrderDetail;
use App\Services\LogService;

class SalesOrderDetailObserver
{
    /**
     * Handle new transaction detail creation
     */
    public function created(SalesOrderDetail $detail): void
    {
        LogService::pos('Transaction detail added', [
            'detail_id' => $detail->id,
            'transaction_id' => $detail->transaction_id,
            'item_id' => $detail->item_id,
            'quantity' => $detail->quantity,
            'unit_price' => $detail->unit_price,
            'subtotal' => $detail->subtotal,
            'added_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle quantity updates
     */
    public function quantityUpdated(TransactionDetail $detail): void
    {
        LogService::pos('Transaction detail quantity updated', [
            'detail_id' => $detail->id,
            'transaction_id' => $detail->transaction_id,
            'item_id' => $detail->item_id,
            'old_quantity' => $detail->getOriginal('quantity'),
            'new_quantity' => $detail->quantity,
            'reason' => $detail->quantity_change_reason,
            'updated_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle price adjustments
     */
    public function priceAdjusted(TransactionDetail $detail): void
    {
        LogService::pos('Transaction detail price adjusted', [
            'detail_id' => $detail->id,
            'transaction_id' => $detail->transaction_id,
            'item_id' => $detail->item_id,
            'old_price' => $detail->getOriginal('unit_price'),
            'new_price' => $detail->unit_price,
            'adjustment_reason' => $detail->price_adjustment_reason,
            'adjusted_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle item discounts
     */
    public function discountApplied(TransactionDetail $detail): void
    {
        LogService::pos('Transaction detail discount applied', [
            'detail_id' => $detail->id,
            'transaction_id' => $detail->transaction_id,
            'item_id' => $detail->item_id,
            'discount_type' => $detail->discount_type,
            'discount_amount' => $detail->discount_amount,
            'original_price' => $detail->original_price,
            'final_price' => $detail->unit_price,
            'applied_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle item cancellation
     */
    public function cancelled(TransactionDetail $detail): void
    {
        LogService::pos('Transaction detail cancelled', [
            'detail_id' => $detail->id,
            'transaction_id' => $detail->transaction_id,
            'item_id' => $detail->item_id,
            'quantity' => $detail->quantity,
            'subtotal' => $detail->subtotal,
            'cancellation_reason' => $detail->cancellation_reason,
            'cancelled_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle tax calculations
     */
    public function taxCalculated(TransactionDetail $detail): void
    {
        LogService::pos('Transaction detail tax calculated', [
            'detail_id' => $detail->id,
            'transaction_id' => $detail->transaction_id,
            'item_id' => $detail->item_id,
            'tax_rate' => $detail->tax_rate,
            'tax_amount' => $detail->tax_amount,
            'taxable_amount' => $detail->taxable_amount,
            'tax_category' => $detail->tax_category,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle serial number tracking
     */
    public function serialNumberAssigned(TransactionDetail $detail): void
    {
        LogService::pos('Serial number assigned to transaction detail', [
            'detail_id' => $detail->id,
            'transaction_id' => $detail->transaction_id,
            'item_id' => $detail->item_id,
            'serial_numbers' => $detail->serial_numbers,
            'batch_numbers' => $detail->batch_numbers,
            'expiry_dates' => $detail->expiry_dates,
            'assigned_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
