<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\LogService;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        if ($product->stock < 10) {
            LogService::pos('Low stock alert for new product', [
                'product_id' => $product->id,
                'name' => $product->name,              'current_stock' => $product->stock,
                'category' => $product->category?->name
            ]);
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Check if stock was updated
        if ($product->isDirty('stock')) {
            $oldStock = $product->getOriginal('stock');
            $newStock = $product->stock;

            // Log stock changes
            LogService::pos('Product stock updated', [
                'product_id' => $product->id,
                'name' => $product->name,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'difference' => $newStock - $oldStock,
                'updated_by' => auth()->id()
            ]);

            // Alert if stock is low
            if ($newStock < 10 && ($oldStock >= 10 || $oldStock === null)) {
                LogService::pos('Product stock below threshold', [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'current_stock' => $newStock,
                    'category' => $product->category?->name,
                    'last_updated' => now()->toDateTimeString()
                ]);
            }
        }

        // Log price changes
        if ($product->isDirty('price')) {
            LogService::pos('Product price updated', [
                'product_id' => $product->id,
                'name' => $product->name,
                'old_price' => $product->getOriginal('price'),
                'new_price' => $product->price,
                'updated_by' => auth()->id()
            ]);
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        LogService::pos('Product deleted', [
            'product_id' => $product->id,
            'name' => $product->name,
            'last_stock' => $product->stock,
            'category' => $product->category?->name,
            'deleted_by' => auth()->id()
        ]);
    }
}
