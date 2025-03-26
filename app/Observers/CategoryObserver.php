<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\LogService;

class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        LogService::pos('New medicine category created', [
            'category_id' => $category->id,
            'name' => $category->name,
            'created_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        LogService::pos('Product category updated', [
            'category_id' => $category->id,
            'name' => $category->name,
            'old_name' => $category->getOriginal('name'),
            'updated_by' => auth()->id(),
            'changes' => array_diff_assoc($category->getAttributes(), $category->getOriginal()),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        // Get count of medicines in this category before deletion
        $medicineCount = $category->medicines()->count();

        LogService::pos('Product category deleted', [
            'category_id' => $category->id,
            'name' => $category->name,
            'deleted_by' => auth()->id(),
            'affected_medicines' => $medicineCount,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
