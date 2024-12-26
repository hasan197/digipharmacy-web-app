<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Transaction;
use App\Models\Customer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalMedicines = Medicine::count();
        $totalCustomers = Customer::count();
        $totalTransactions = Transaction::count();
        $recentTransactions = Transaction::with(['customer', 'details.medicine'])
            ->latest()
            ->take(5)
            ->get();
        $lowStockMedicines = Medicine::where('stock', '<', 10)->get();

        return view('dashboard.index', compact(
            'totalMedicines',
            'totalCustomers',
            'totalTransactions',
            'recentTransactions',
            'lowStockMedicines'
        ));
    }
}
