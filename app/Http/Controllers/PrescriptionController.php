<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PrescriptionController extends Controller
{
    public function index()
    {
        $prescriptions = Prescription::with(['items', 'pharmacist'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($prescriptions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patientName' => 'required|string|max:255',
            'doctorName' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.medicineId' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.dosage' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'discount' => 'required|numeric|min:0|max:100',
            'additionalFee' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $prescription = Prescription::create([
                'patient_name' => $validated['patientName'],
                'doctor_name' => $validated['doctorName'],
                'notes' => $validated['notes'] ?? null,
                'discount' => $validated['discount'],
                'additional_fee' => $validated['additionalFee'],
                'total' => $validated['total'],
                'status' => 'pending',
                'created_by' => Auth::id()
            ]);

            foreach ($validated['items'] as $item) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'medicine_id' => $item['medicineId'],
                    'quantity' => $item['quantity'],
                    'dosage' => $item['dosage'],
                    'price' => $item['price']
                ]);
            }

            DB::commit();
            return response()->json($prescription->load('items'), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating prescription'], 500);
        }
    }

    public function show($id)
    {
        $prescription = Prescription::with(['items', 'pharmacist'])
            ->findOrFail($id);

        return response()->json($prescription);
    }

    public function validate(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:validated,rejected',
            'pharmacist_notes' => 'nullable|string'
        ]);

        $prescription = Prescription::findOrFail($id);

        // Only pending prescriptions can be validated
        if ($prescription->status !== 'pending') {
            return response()->json(['message' => 'Prescription has already been processed'], 422);
        }

        // Only pharmacists can validate prescriptions
        if (!Auth::user()->hasRole('pharmacist')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            $prescription->update([
                'status' => $validated['status'],
                'pharmacist_notes' => $validated['pharmacist_notes'],
                'validated_by' => Auth::id(),
                'validated_at' => now()
            ]);

            // If validated, update medicine stock
            if ($validated['status'] === 'validated') {
                foreach ($prescription->items as $item) {
                    $medicine = $item->medicine;
                    $medicine->stock -= $item->quantity;
                    $medicine->save();
                }
            }

            DB::commit();
            return response()->json($prescription->load(['items', 'pharmacist']));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error validating prescription'], 500);
        }
    }
}
