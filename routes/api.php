<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductManagementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });
});

// Protected routes
Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/products', [ProductController::class, 'getProducts']);
    Route::get('/products/search', [ProductController::class, 'searchProducts']);
    // Sales routes
    Route::get('/sales/pending', [SalesController::class, 'getPendingSales']);
    Route::get('/sales/recent', [SalesController::class, 'getRecentSales']);
    Route::get('/sales/resume/{id}', [SalesController::class, 'resumeSale']);
    Route::delete('/sales/pending/{id}', [SalesController::class, 'deletePendingSale']);
    Route::get('/sales/date-range', [SalesController::class, 'getSalesByDateRange']);
    Route::get('/sales/customer/{id}', [SalesController::class, 'getSalesByCustomerId']);
    Route::get('/sales', [SalesController::class, 'index']);
    Route::post('/sales', [SalesController::class, 'store']);
    Route::get('/sales/{id}', [SalesController::class, 'show']);
    Route::post('/checkout', [CheckoutController::class, 'checkout']);

    // Customer routes
    Route::get('/customers', [CustomerController::class, 'apiIndex']);
    Route::get('/customers/search', [CustomerController::class, 'search']);
    Route::post('/customers', [CustomerController::class, 'apiStore']);
    Route::get('/customers/{id}', [CustomerController::class, 'apiShow']);
    Route::put('/customers/{id}', [CustomerController::class, 'apiUpdate']);
    Route::delete('/customers/{id}', [CustomerController::class, 'apiDestroy']);

    // Inventory routes
    Route::group(['prefix' => 'inventory'], function () {
        Route::get('/transactions', [InventoryController::class, 'getLatestTransactions']);
        Route::get('/transactions/{id}', [InventoryController::class, 'getTransactionById']);
        Route::get('/product/{productId}/transactions', [InventoryController::class, 'getProductTransactionHistory']);
        Route::get('/transactions/date-range', [InventoryController::class, 'getTransactionsByDateRange']);
        Route::get('/low-stock', [InventoryController::class, 'getLowStockProducts']);
        Route::post('/stock-in', [InventoryController::class, 'recordStockIn']);
        Route::post('/stock-out', [InventoryController::class, 'recordStockOut']);
        Route::post('/adjust-stock', [InventoryController::class, 'adjustStock']);
        Route::get('/', [InventoryController::class, 'index']);
    });

    // Role Management Routes - Only accessible by admin
    Route::middleware(['admin'])->group(function () {
        Route::get('/roles', [RoleManagementController::class, 'index']);
        Route::post('/roles', [RoleManagementController::class, 'store']);
        Route::get('/roles/{role}', [RoleManagementController::class, 'show']);
        Route::post('/roles/update/{role}', [RoleManagementController::class, 'update']);
        Route::post('/roles/delete/{role}', [RoleManagementController::class, 'destroy']);
    });

    // Product Management Routes
    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [ProductManagementController::class, 'index']);
        Route::post('/', [ProductManagementController::class, 'store']);
        Route::get('/{id}', [ProductManagementController::class, 'show']);
        Route::put('/{id}', [ProductManagementController::class, 'update']);
        Route::delete('/{id}', [ProductManagementController::class, 'destroy']);
        Route::post('/{id}/status', [ProductManagementController::class, 'changeStatus']);
        Route::get('/category/{categoryId}', [ProductManagementController::class, 'getByCategory']);
        Route::get('/search', [ProductManagementController::class, 'search']);
        Route::get('/low-stock', [ProductManagementController::class, 'getLowStock']);
    });
});