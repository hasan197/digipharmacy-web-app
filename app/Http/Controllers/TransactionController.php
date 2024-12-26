<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Medicine;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['customer', 'details.medicine'])
            ->latest()
            ->paginate(10);
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $customers = Customer::all();
        $medicines = Medicine::where('stock', '>', 0)->get();
        return view('transactions.create', compact('customers', 'medicines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'medicines' => 'required|array',
            'medicines.*.id' => 'required|exists:medicines,id',
            'medicines.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $total_amount = 0;
            foreach ($validated['medicines'] as $item) {
                $medicine = Medicine::findOrFail($item['id']);
                if ($medicine->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$medicine->name}");
                }
                $total_amount += $medicine->price * $item['quantity'];
            }

            $transaction = Transaction::create([
                'customer_id' => $validated['customer_id'],
                'total_amount' => $total_amount,
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null
            ]);

            foreach ($validated['medicines'] as $item) {
                $medicine = Medicine::findOrFail($item['id']);
                $transaction->details()->create([
                    'medicine_id' => $medicine->id,
                    'quantity' => $item['quantity'],
                    'price' => $medicine->price,
                    'subtotal' => $medicine->price * $item['quantity']
                ]);

                $medicine->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['customer', 'details.medicine']);
        return view('transactions.show', compact('transaction'));
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }
}
