<?php

namespace App\Observers;

use App\Models\User;
use App\Services\LogService;
use Illuminate\Support\Arr;

class UserActivityObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        LogService::auth('New user account created', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_by' => auth()->id() ?? 'system',
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Don't log password changes for security
        $changes = array_diff_assoc(
            Arr::except($user->getAttributes(), ['password']),
            Arr::except($user->getOriginal(), ['password'])
        );

        if (!empty($changes)) {
            LogService::auth('User account updated', [
                'user_id' => $user->id,
                'name' => $user->name,
                'updated_by' => auth()->id(),
                'changes' => $changes,
                'timestamp' => now()->toDateTimeString()
            ]);
        }

        // Separately log role/permission changes
        if ($user->isDirty('role')) {
            LogService::auth('User role changed', [
                'user_id' => $user->id,
                'name' => $user->name,
                'old_role' => $user->getOriginal('role'),
                'new_role' => $user->role,
                'changed_by' => auth()->id(),
                'timestamp' => now()->toDateTimeString()
            ]);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        LogService::auth('User account deleted', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'deleted_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        LogService::auth('User account restored', [
            'user_id' => $user->id,
            'name' => $user->name,
            'restored_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        LogService::auth('User account permanently deleted', [
            'user_id' => $user->id,
            'name' => $user->name,
            'deleted_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
