<?php

namespace App\Observers;

use App\Models\PrescriptionItem;
use App\Services\LogService;

class PrescriptionItemObserver
{
    /**
     * Handle new prescription item creation
     */
    public function created(PrescriptionItem $item): void
    {
        LogService::pos('Prescription item added', [
            'item_id' => $item->id,
            'prescription_id' => $item->prescription_id,
            'medicine_id' => $item->medicine_id,
            'dosage' => $item->dosage,
            'frequency' => $item->frequency,
            'duration' => $item->duration,
            'instructions' => $item->instructions,
            'prescribed_by' => $item->prescribed_by,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle medicine substitution
     */
    public function medicineSubstituted(PrescriptionItem $item): void
    {
        LogService::pos('Prescription medicine substituted', [
            'item_id' => $item->id,
            'prescription_id' => $item->prescription_id,
            'original_medicine_id' => $item->getOriginal('medicine_id'),
            'new_medicine_id' => $item->medicine_id,
            'substitution_reason' => $item->substitution_reason,
            'approved_by' => $item->substitution_approved_by,
            'pharmacist_notes' => $item->pharmacist_notes,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle dosage adjustments
     */
    public function dosageAdjusted(PrescriptionItem $item): void
    {
        LogService::pos('Prescription dosage adjusted', [
            'item_id' => $item->id,
            'prescription_id' => $item->prescription_id,
            'medicine_id' => $item->medicine_id,
            'old_dosage' => $item->getOriginal('dosage'),
            'new_dosage' => $item->dosage,
            'adjustment_reason' => $item->dosage_adjustment_reason,
            'adjusted_by' => auth()->id(),
            'prescriber_notified' => $item->prescriber_notification_sent,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle drug interactions
     */
    public function interactionDetected(PrescriptionItem $item, array $interactions): void
    {
        LogService::error('Drug interaction detected', [
            'item_id' => $item->id,
            'prescription_id' => $item->prescription_id,
            'medicine_id' => $item->medicine_id,
            'interacting_medicines' => $interactions['medicines'],
            'severity' => $interactions['severity'],
            'effects' => $interactions['effects'],
            'recommendations' => $interactions['recommendations'],
            'detected_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle contraindications
     */
    public function contraindicationDetected(PrescriptionItem $item, array $contraindications): void
    {
        LogService::error('Contraindication detected', [
            'item_id' => $item->id,
            'prescription_id' => $item->prescription_id,
            'medicine_id' => $item->medicine_id,
            'patient_condition' => $contraindications['condition'],
            'risk_level' => $contraindications['risk_level'],
            'warning' => $contraindications['warning'],
            'alternatives' => $contraindications['alternatives'],
            'detected_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle dispensing
     */
    public function dispensed(PrescriptionItem $item): void
    {
        LogService::pos('Prescription item dispensed', [
            'item_id' => $item->id,
            'prescription_id' => $item->prescription_id,
            'medicine_id' => $item->medicine_id,
            'quantity_dispensed' => $item->quantity_dispensed,
            'batch_number' => $item->batch_number,
            'expiry_date' => $item->expiry_date,
            'dispensed_by' => auth()->id(),
            'verification_status' => $item->verification_status,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle counseling notes
     */
    public function counselingProvided(PrescriptionItem $item): void
    {
        LogService::pos('Patient counseling provided', [
            'item_id' => $item->id,
            'prescription_id' => $item->prescription_id,
            'medicine_id' => $item->medicine_id,
            'counseling_points' => $item->counseling_points,
            'patient_understanding' => $item->patient_understanding_level,
            'follow_up_needed' => $item->follow_up_required,
            'counseled_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
