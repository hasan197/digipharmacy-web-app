<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Exceptions\UnauthorizedException;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class AccessControlService
{
    private PermissionValidationService $permissionValidator;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        $this->permissionValidator = new PermissionValidationService();
        $this->userRepository = $userRepository;
    }

    /**
     * Validates if current user can access a specific feature
     * 
     * @param string $domain Domain of the feature (e.g., 'inventory', 'pos')
     * @param string $action Action being performed (e.g., 'view', 'create')
     * @param array $context Additional context data for complex validations
     * @throws UnauthorizedException if user doesn't have required permissions
     * @return bool
     */
    public function validateFeatureAccess(string $domain, string $action, array $context = []): bool
    {
        // Get current authenticated user
        $user = Auth::user();
        if (!$user) {
            throw new UnauthorizedException("User not authenticated");
        }

        // Check if user is active
        if (!$this->isUserActive($user)) {
            throw new UnauthorizedException("User account is not active");
        }

        // Get required permissions for the feature
        $requiredPermissions = $this->permissionValidator->getFeaturePermissions($domain, $action);
        
        // Check if user has any of the required permissions
        if (!$user->hasAnyPermission($requiredPermissions)) {
            $this->logAccessDenied($user, $domain, $action, $context);
            throw new UnauthorizedException("Access denied to {$domain}.{$action}");
        }

        return true;
    }

    /**
     * Checks if user account is active
     */
    private function isUserActive($user): bool
    {
        // Handle both domain and eloquent models
        if ($user instanceof \App\Domain\Auth\Models\User) {
            return $user->isActive() && !$user->isBlocked();
        }

        if ($user instanceof \App\Models\User) {
            return $user->is_active && !$user->is_blocked;
        }

        throw new \InvalidArgumentException('Invalid user type provided');
    }

    /**
     * Logs access denied events
     */
    private function logAccessDenied($user, string $domain, string $action, array $context): void
    {
        // Handle both domain and eloquent models
        $userId = $user->id;
        $userEmail = $user instanceof \App\Domain\Auth\Models\User ? $user->getEmail() : $user->email;

        // Log access denied event
        \Log::warning('Access denied', [
            'user_id' => $userId,
            'user_email' => $userEmail,
            'domain' => $domain,
            'action' => $action,
            'context' => $context,
            'timestamp' => now()
        ]);
    }
}
