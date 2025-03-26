<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\LogService;

class TransactionObserver
{
    /**
     * Handle new transaction creation
     */
    public function created(Transaction $transaction): void
    {
        LogService::pos('New transaction created', [
            'transaction_id' => $transaction->id,
            'type' => $transaction->type,
            'amount' => $transaction->amount,
            'reference' => $transaction->reference_number,
            'created_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle payment status updates
     */
    public function paymentStatusUpdated(Transaction $transaction): void
    {
        LogService::pos('Transaction payment status updated', [
            'transaction_id' => $transaction->id,
            'old_status' => $transaction->getOriginal('payment_status'),
            'new_status' => $transaction->payment_status,
            'payment_method' => $transaction->payment_method,
            'updated_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);

        if ($transaction->payment_status === 'failed') {
            LogService::error('Transaction payment failed', [
                'transaction_id' => $transaction->id,
                'error_code' => $transaction->error_code,
                'error_message' => $transaction->error_message,
                'timestamp' => now()->toDateTimeString()
            ]);
        }
    }

    /**
     * Handle transaction reconciliation
     */
    public function reconciled(Transaction $transaction): void
    {
        LogService::pos('Transaction reconciled', [
            'transaction_id' => $transaction->id,
            'reconciliation_date' => $transaction->reconciliation_date,
            'reconciled_by' => auth()->id(),
            'discrepancies' => $transaction->discrepancies,
            'notes' => $transaction->reconciliation_notes,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle transaction void
     */
    public function voided(Transaction $transaction): void
    {
        LogService::pos('Transaction voided', [
            'transaction_id' => $transaction->id,
            'original_amount' => $transaction->amount,
            'void_reason' => $transaction->void_reason,
            'voided_by' => auth()->id(),
            'approval_reference' => $transaction->void_approval_ref,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle refund processing
     */
    public function refunded(Transaction $transaction): void
    {
        LogService::pos('Transaction refunded', [
            'transaction_id' => $transaction->id,
            'refund_amount' => $transaction->refund_amount,
            'original_amount' => $transaction->amount,
            'refund_reason' => $transaction->refund_reason,
            'refund_method' => $transaction->refund_method,
            'refunded_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle transaction verification
     */
    public function verified(Transaction $transaction): void
    {
        LogService::pos('Transaction verified', [
            'transaction_id' => $transaction->id,
            'verification_method' => $transaction->verification_method,
            'verified_by' => auth()->id(),
            'verification_notes' => $transaction->verification_notes,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle transaction disputes
     */
    public function disputed(Transaction $transaction): void
    {
        LogService::error('Transaction disputed', [
            'transaction_id' => $transaction->id,
            'dispute_reason' => $transaction->dispute_reason,
            'dispute_amount' => $transaction->dispute_amount,
            'reported_by' => $transaction->dispute_reporter_id,
            'evidence' => $transaction->dispute_evidence,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
