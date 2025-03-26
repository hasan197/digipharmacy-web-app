<?php

namespace App\Observers;

use App\Models\SalesOrder;
use App\Services\LogService;

class SalesOrderObserver
{
    /**
     * Handle new sale creation
     */
    public function created(SalesOrder $salesOrder): void
    {
        LogService::pos('New sale created', [
            'sale_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'total' => $salesOrder->total,
            'payment_method' => $salesOrder->payment_method,
            'items_count' => $salesOrder->details()->count(),
            'cashier_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle sale status updates
     */
    public function statusUpdated(SalesOrder $salesOrder): void
    {
        LogService::pos('Sale status updated', [
            'sale_id' => $salesOrder->id,
            'old_status' => $salesOrder->getOriginal('status'),
            'new_status' => $salesOrder->status,
            'reason' => $salesOrder->status_change_reason,
            'updated_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle sale cancellation
     */
    public function cancelled(SalesOrder $salesOrder): void
    {
        LogService::pos('Sale cancelled', [
            'sale_id' => $salesOrder->id,
            'total' => $salesOrder->total,
            'reason' => $salesOrder->cancellation_reason,
            'cancelled_by' => auth()->id(),
            'original_cashier' => $salesOrder->cashier_id,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle payment processing
     */
    public function paymentProcessed(SalesOrder $salesOrder): void
    {
        LogService::pos('Sale payment processed', [
            'sale_id' => $salesOrder->id,
            'amount' => $salesOrder->total,
            'payment_method' => $salesOrder->payment_method,
            'payment_status' => $salesOrder->payment_status,
            'reference_number' => $salesOrder->payment_reference,
            'processed_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);

        if ($salesOrder->payment_status === 'failed') {
            LogService::error('Sale payment failed', [
                'sale_id' => $salesOrder->id,
                'error' => $salesOrder->payment_error,
                'attempt' => $salesOrder->payment_attempt,
                'timestamp' => now()->toDateTimeString()
            ]);
        }
    }

    /**
     * Handle discount application
     */
    public function discountApplied(SalesOrder $salesOrder): void
    {
        LogService::pos('Discount applied to sale', [
            'sale_id' => $salesOrder->id,
            'discount_amount' => $salesOrder->discount,
            'original_amount' => $salesOrder->total,
            'final_amount' => $salesOrder->grand_total,
            'promotion_code' => $salesOrder->promotion_code,
            'applied_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle delivery status updates
     */
    public function deliveryUpdated(SalesOrder $salesOrder): void
    {
        LogService::pos('Sale delivery status updated', [
            'sale_id' => $salesOrder->id,
            'delivery_status' => $salesOrder->delivery_status,
            'tracking_number' => $salesOrder->tracking_number,
            'delivery_address' => $salesOrder->delivery_address,
            'estimated_delivery' => $salesOrder->estimated_delivery_date,
            'updated_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle receipt generation
     */
    public function receiptGenerated(SalesOrder $salesOrder): void
    {
        LogService::pos('Sale receipt generated', [
            'sale_id' => $salesOrder->id,
            'receipt_number' => $salesOrder->receipt_number,
            'format' => $salesOrder->receipt_format,
            'generated_by' => auth()->id(),
            'customer_copy' => $salesOrder->customer_copy_sent,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
