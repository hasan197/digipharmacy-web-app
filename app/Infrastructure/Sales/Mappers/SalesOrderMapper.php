<?php

namespace App\Infrastructure\Sales\Mappers;

use App\Domain\Sales\Models\SalesOrder as DomainSalesOrder;
use App\Domain\Sales\Models\SalesOrderDetail as DomainSalesOrderDetail;
use App\Domain\Sales\ValueObjects\PaymentDetails;
use App\Models\SalesOrder as EloquentSalesOrder;
use App\Models\SalesOrderDetail as EloquentSalesOrderDetail;

class SalesOrderMapper
{
    public function toDomain(EloquentSalesOrder $eloquentSalesOrder): DomainSalesOrder
    {
        // Convert payment details to value object
        $paymentDetailsArray = $eloquentSalesOrder->payment_details ?? [];
        
        // Extract specific fields from payment_details
        $method = $paymentDetailsArray['method'] ?? $eloquentSalesOrder->payment_method ?? '';
        $isValid = $paymentDetailsArray['isValid'] ?? true;
        $cardLast4 = $paymentDetailsArray['cardLast4'] ?? null;
        $cardType = $paymentDetailsArray['cardType'] ?? null;
        $approvalCode = $paymentDetailsArray['approvalCode'] ?? null;
        $transactionId = $paymentDetailsArray['transactionId'] ?? null;
        $amountPaid = isset($paymentDetailsArray['amountPaid']) ? (float)$paymentDetailsArray['amountPaid'] : 0;
        $change = isset($paymentDetailsArray['change']) ? (float)$paymentDetailsArray['change'] : 0;
        
        // Collect other fields
        $otherData = [];
        foreach ($paymentDetailsArray as $key => $value) {
            if (!in_array($key, ['method', 'isValid', 'cardLast4', 'cardType', 'approvalCode', 'transactionId', 'amountPaid', 'change'])) {
                $otherData[$key] = $value;
            }
        }
        
        // Create PaymentDetails object with the new constructor
        $paymentDetails = new PaymentDetails(
            $method,
            $isValid,
            $cardLast4,
            $cardType,
            $approvalCode,
            $transactionId,
            $amountPaid,
            $change,
            $otherData
        );
        
        // Create domain sales order
        $salesOrder = DomainSalesOrder::create(
            $eloquentSalesOrder->customer_id,
            $eloquentSalesOrder->customer_name ?? '',
            $eloquentSalesOrder->customer_phone ?? '',
            $eloquentSalesOrder->total,
            $eloquentSalesOrder->discount,
            $eloquentSalesOrder->additional_fee,
            $eloquentSalesOrder->grand_total,
            $eloquentSalesOrder->payment_method,
            $paymentDetails,
            $eloquentSalesOrder->status,
            $eloquentSalesOrder->notes,
            $eloquentSalesOrder->invoice_number,
            $eloquentSalesOrder->id,
            $eloquentSalesOrder->created_at ? new \DateTime($eloquentSalesOrder->created_at) : null,
            $eloquentSalesOrder->updated_at ? new \DateTime($eloquentSalesOrder->updated_at) : null
        );
        
        // Add order details
        if ($eloquentSalesOrder->details) {
            foreach ($eloquentSalesOrder->details as $detail) {
                $orderDetail = DomainSalesOrderDetail::create(
                    $detail->product_id,
                    $detail->quantity,
                    $detail->price,
                    $detail->subtotal,
                    $detail->sales_order_id,
                    $detail->id,
                    $detail->created_at ? new \DateTime($detail->created_at) : null,
                    $detail->updated_at ? new \DateTime($detail->updated_at) : null
                );
                
                $salesOrder->addOrderDetail($orderDetail);
            }
        }
        
        return $salesOrder;
    }

    public function toEloquent(DomainSalesOrder $domainSalesOrder): EloquentSalesOrder
    {
        $salesOrder = new EloquentSalesOrder();
        
        if ($domainSalesOrder->getId()) {
            $salesOrder = EloquentSalesOrder::find($domainSalesOrder->getId()) ?? $salesOrder;
        }
        
        $salesOrder->invoice_number = $domainSalesOrder->getInvoiceNumber();
        $salesOrder->customer_id = $domainSalesOrder->getCustomerId();
        $salesOrder->customer_name = $domainSalesOrder->getCustomerName();
        $salesOrder->customer_phone = $domainSalesOrder->getCustomerPhone();
        $salesOrder->total = $domainSalesOrder->getTotal();
        $salesOrder->discount = $domainSalesOrder->getDiscount();
        $salesOrder->additional_fee = $domainSalesOrder->getAdditionalFee();
        $salesOrder->grand_total = $domainSalesOrder->getGrandTotal();
        $salesOrder->payment_method = $domainSalesOrder->getPaymentMethod();
        $salesOrder->payment_details = $domainSalesOrder->getPaymentDetails()->toArray();
        $salesOrder->status = $domainSalesOrder->getStatus();
        $salesOrder->notes = $domainSalesOrder->getNotes();
        
        // Save the sales order first to get an ID if it's new
        $salesOrder->save();
        
        // Handle order details
        $orderDetails = $domainSalesOrder->getOrderDetails();
        
        foreach ($orderDetails as $domainDetail) {
            $detail = new EloquentSalesOrderDetail();
            
            if ($domainDetail->getId()) {
                $detail = EloquentSalesOrderDetail::find($domainDetail->getId()) ?? $detail;
            }
            
            $detail->sales_order_id = $salesOrder->id;
            $detail->product_id = $domainDetail->getProductId();
            $detail->quantity = $domainDetail->getQuantity();
            $detail->price = $domainDetail->getPrice();
            $detail->subtotal = $domainDetail->getSubtotal();
            
            $detail->save();
        }
        
        return $salesOrder;
    }
}
