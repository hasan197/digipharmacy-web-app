<?php

namespace App\Observers;

use App\Models\Customer;
use App\Services\LogService;

class CustomerObserver
{
    /**
     * Handle customer creation event
     */
    public function created(Customer $customer): void
    {
        LogService::pos('Customer account created', [
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'membership_type' => $customer->membership_type,
            'created_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle customer profile updates
     */
    public function updated(Customer $customer): void
    {
        LogService::pos('Customer profile updated', [
            'customer_id' => $customer->id,
            'changes' => [
                'name' => [
                    'old' => $customer->getOriginal('name'),
                    'new' => $customer->name
                ],
                'email' => [
                    'old' => $customer->getOriginal('email'),
                    'new' => $customer->email
                ],
                'phone' => [
                    'old' => $customer->getOriginal('phone'),
                    'new' => $customer->phone
                ]
            ],
            'updated_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle membership changes
     */
    public function membershipChanged(Customer $customer): void
    {
        LogService::pos('Customer membership changed', [
            'customer_id' => $customer->id,
            'old_type' => $customer->getOriginal('membership_type'),
            'new_type' => $customer->membership_type,
            'points' => $customer->loyalty_points,
            'changed_by' => auth()->id(),
            'reason' => $customer->membership_change_reason,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle loyalty points updates
     */
    public function pointsUpdated(Customer $customer, array $details): void
    {
        LogService::pos('Customer loyalty points updated', [
            'customer_id' => $customer->id,
            'old_points' => $details['old_points'],
            'points_change' => $details['points_change'],
            'new_points' => $customer->loyalty_points,
            'transaction_id' => $details['transaction_id'],
            'reason' => $details['reason'],
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle customer preferences
     */
    public function preferencesUpdated(Customer $customer): void
    {
        LogService::pos('Customer preferences updated', [
            'customer_id' => $customer->id,
            'preferences' => [
                'notification_channel' => $customer->notification_preference,
                'language' => $customer->language_preference,
                'marketing_opt_in' => $customer->marketing_opt_in
            ],
            'updated_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle customer account deactivation
     */
    public function deleted(Customer $customer): void
    {
        LogService::pos('Customer account deactivated', [
            'customer_id' => $customer->id,
            'reason' => $customer->deactivation_reason,
            'deactivated_by' => auth()->id(),
            'last_transaction' => $customer->last_transaction_date,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle customer medical history updates
     */
    public function medicalHistoryUpdated(Customer $customer): void
    {
        LogService::pos('Customer medical history updated', [
            'customer_id' => $customer->id,
            'allergies' => $customer->allergies,
            'conditions' => $customer->medical_conditions,
            'medications' => $customer->current_medications,
            'updated_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
