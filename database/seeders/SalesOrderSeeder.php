<?php

namespace Database\Seeders;

use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\Product;
use App\Models\Customer;
use App\Models\InventoryTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesOrderSeeder extends Seeder
{
    public function run()
    {
        // Get available products and customers
        $products = Product::all();
        $customers = Customer::all();
        
        if ($products->isEmpty()) {
            $this->command->error('No products available for seeding sales orders.');
            return;
        }
        
        if ($customers->isEmpty()) {
            $this->command->error('No customers available for seeding sales orders.');
            return;
        }

        DB::beginTransaction();
        try {
            SalesOrder::withoutInvoiceNumber(function () use ($products, $customers) {
                // Create sample orders for the last 3 days
                for ($i = 1; $i <= 3; $i++) {
                    // Create orders for different days
                    $date = now()->subDays($i - 1);
                    
                    // Generate invoice number
                    $latestInvoice = SalesOrder::whereDate('created_at', $date)
                        ->latest()
                        ->first();

                    $sequence = $latestInvoice ? (int)substr($latestInvoice->invoice_number, -4) + 1 : 1;
                    $invoiceNumber = sprintf(
                        'INV/%s/%04d',
                        $date->format('Ymd'),
                        $sequence
                    );
                    
                    // Get random customer
                    $customer = $customers->random();
                    
                    // Generate random payment method
                    $paymentMethods = ['cash', 'debit', 'credit', 'qris'];
                    $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                    
                    // Create payment details based on method
                    $paymentDetails = $this->generatePaymentDetails($paymentMethod);
                    
                    $order = new SalesOrder([
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'customer_phone' => $customer->phone,
                        'total' => 0,
                        'discount' => 0,
                        'additional_fee' => 0,
                        'grand_total' => 0,
                        'payment_method' => $paymentMethod,
                        'payment_details' => $paymentDetails,
                        'status' => 'completed',
                        'notes' => 'Sample order #' . $i,
                        'invoice_number' => $invoiceNumber
                    ]);
                    
                    $order->created_at = $date;
                    $order->updated_at = $date;
                    $order->save();

                    // Add 2-3 random products to each order
                    $orderTotal = 0;
                    $numProducts = rand(2, 3);
                    $selectedProducts = $products->random($numProducts);
                    
                    foreach ($selectedProducts as $product) {
                        $quantity = rand(1, 3);
                        $subtotal = $product->price * $quantity;
                        $orderTotal += $subtotal;

                        // Create order detail
                        SalesOrderDetail::create([
                            'sales_order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                            'price' => $product->price,
                            'subtotal' => $subtotal
                        ]);

                        // Create inventory transaction
                        InventoryTransaction::create([
                            'product_id' => $product->id,
                            'quantity' => -$quantity, // negative for sales
                            'type' => 'sale',
                            'reference_id' => $order->id,
                            'reference_type' => 'App\\Models\\SalesOrder',
                            'notes' => 'Sale from order #' . $order->invoice_number,
                            'created_at' => $date,
                            'updated_at' => $date
                        ]);
                    }

                    // Calculate tax and grand total
                    $tax = round($orderTotal * 0.1); // 10% tax
                    
                    // Update order totals
                    $order->update([
                        'total' => $orderTotal,
                        'tax' => $tax,
                        'grand_total' => $orderTotal + $tax
                    ]);
                }
            });
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Error seeding sales orders: ' . $e->getMessage());
        }
    }

    private function generatePaymentDetails(string $method): array
    {
        $details = [
            'method' => $method,
            'isValid' => true
        ];

        switch ($method) {
            case 'cash':
                $details['amountPaid'] = rand(100000, 1000000);
                $details['change'] = rand(0, 50000);
                break;
            
            case 'debit':
            case 'credit':
                $details['cardType'] = $method === 'debit' ? 'Debit Card' : 'Credit Card';
                $details['cardLast4'] = sprintf('%04d', rand(1000, 9999));
                $details['approvalCode'] = strtoupper(substr(md5(rand()), 0, 6));
                break;
            
            case 'qris':
                $details['transactionId'] = 'QRIS' . time() . rand(1000, 9999);
                break;
        }

        return $details;
    }
}
