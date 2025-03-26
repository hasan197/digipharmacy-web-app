<?php

namespace App\Observers;

use App\Models\Prescription;
use App\Services\LogService;

class PrescriptionObserver
{
    /**
     * Handle the Prescription "created" event.
     */
    public function created(Prescription $prescription): void
    {
        LogService::pos('New prescription recorded', [
            'prescription_id' => $prescription->id,
            'patient_id' => $prescription->patient_id,
            'doctor_name' => $prescription->doctor_name,
            'medicine_count' => $prescription->items->count(),
            'issue_date' => $prescription->issue_date,
            'expiry_date' => $prescription->expiry_date,
            'recorded_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);

        // Log controlled substances separately
        $controlledItems = $prescription->items->filter(function ($item) {
            return $item->medicine->is_controlled;
        });

        if ($controlledItems->isNotEmpty()) {
            LogService::pos('Controlled substances in prescription', [
                'prescription_id' => $prescription->id,
                'patient_id' => $prescription->patient_id,
                'controlled_items' => $controlledItems->map(function ($item) {
                    return [
                        'medicine_id' => $item->medicine_id,
                        'name' => $item->medicine->name,
                        'quantity' => $item->quantity
                    ];
                })->toArray(),
                'doctor_name' => $prescription->doctor_name,
                'recorded_by' => auth()->id()
            ]);
        }
    }

    /**
     * Handle the Prescription "updated" event.
     */
    public function updated(Prescription $prescription): void
    {
        LogService::pos('Prescription updated', [
            'prescription_id' => $prescription->id,
            'patient_id' => $prescription->patient_id,
            'changes' => array_diff_assoc($prescription->getAttributes(), $prescription->getOriginal()),
            'updated_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);

        // Log status changes separately
        if ($prescription->isDirty('status')) {
            LogService::pos('Prescription status changed', [
                'prescription_id' => $prescription->id,
                'patient_id' => $prescription->patient_id,
                'old_status' => $prescription->getOriginal('status'),
                'new_status' => $prescription->status,
                'changed_by' => auth()->id(),
                'timestamp' => now()->toDateTimeString()
            ]);
        }
    }

    /**
     * Handle prescription verification
     */
    public function verified(Prescription $prescription): void
    {
        LogService::pos('Prescription verified', [
            'prescription_id' => $prescription->id,
            'patient_id' => $prescription->patient_id,
            'doctor_name' => $prescription->doctor_name,
            'verification_method' => $prescription->verification_method,
            'verified_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle prescription dispensing
     */
    public function dispensed(Prescription $prescription, array $details): void
    {
        LogService::pos('Prescription dispensed', [
            'prescription_id' => $prescription->id,
            'patient_id' => $prescription->patient_id,
            'dispensed_items' => $details['items'],
            'total_cost' => $details['total_cost'],
            'payment_method' => $details['payment_method'],
            'dispensed_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle prescription rejection
     */
    public function rejected(Prescription $prescription, string $reason): void
    {
        LogService::pos('Prescription rejected', [
            'prescription_id' => $prescription->id,
            'patient_id' => $prescription->patient_id,
            'doctor_name' => $prescription->doctor_name,
            'rejection_reason' => $reason,
            'rejected_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
