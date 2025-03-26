<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Transaction;
use App\Models\Customer;
use App\Services\LogService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get dashboard statistics
            $totalMedicines = Medicine::count();
            $totalCustomers = Customer::count();
            $totalTransactions = Transaction::count();
            $recentTransactions = Transaction::with(['customer', 'details.medicine'])
                ->latest()
                ->take(5)
                ->get();
            $lowStockMedicines = Medicine::where('stock', '<', 10)->get();

            // Log dashboard access
            LogService::pos('Dashboard accessed', [
                'user_id' => auth()->id(),
                'access_time' => now()->toDateTimeString(),
                'statistics' => [
                    'total_medicines' => $totalMedicines,
                    'total_customers' => $totalCustomers,
                    'total_transactions' => $totalTransactions,
                    'recent_transactions_count' => $recentTransactions->count()
                ]
            ]);

            // Log low stock warnings
            if ($lowStockMedicines->isNotEmpty()) {
                LogService::warning('Low stock medicines detected', [
                    'user_id' => auth()->id(),
                    'medicines' => $lowStockMedicines->map(function ($medicine) {
                        return [
                            'id' => $medicine->id,
                            'name' => $medicine->name,
                            'current_stock' => $medicine->stock,
                            'category' => $medicine->category?->name
                        ];
                    })->toArray(),
                    'detection_time' => now()->toDateTimeString()
                ]);
            }

            // Log system performance metrics
            LogService::pos('System performance metrics', [
                'user_id' => auth()->id(),
                'metrics' => [
                    'response_time' => microtime(true) - LARAVEL_START,
                    'memory_usage' => memory_get_usage(true),
                    'peak_memory' => memory_get_peak_usage(true)
                ],
                'timestamp' => now()->toDateTimeString()
            ]);

            return view('dashboard.index', compact(
                'totalMedicines',
                'totalCustomers',
                'totalTransactions',
                'recentTransactions',
                'lowStockMedicines'
            ));

        } catch (\Exception $e) {
            // Log any errors that occur
            LogService::error('Dashboard error occurred', [
                'user_id' => auth()->id(),
                'error' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'timestamp' => now()->toDateTimeString()
            ]);

            throw $e;
        }
    }
}
