<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Prescription;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\PrescriptionItem;
use App\Observers\ProductObserver;
use App\Observers\CategoryObserver;
use App\Observers\UserActivityObserver;
use App\Observers\PrescriptionObserver;
use App\Observers\CustomerObserver;
use App\Observers\SalesOrderObserver;
use App\Observers\SalesOrderDetailObserver;
use App\Observers\PrescriptionItemObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set timezone
        date_default_timezone_set('Asia/Jakarta');

        // Register model observers
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);
        User::observe(UserActivityObserver::class);
        Prescription::observe(PrescriptionObserver::class);
        Customer::observe(CustomerObserver::class);
        SalesOrder::observe(SalesOrderObserver::class);
        SalesOrderDetail::observe(SalesOrderDetailObserver::class);
        PrescriptionItem::observe(PrescriptionItemObserver::class);
    }
}
