<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Auth;

class PermissionValidationService
{
    /**
     * Map of feature paths to required permissions
     * One permission can map to multiple features
     */
    private static array $featureMap = [
        'inventory.create' => ['inventory.create'],
        'inventory.delete' => ['inventory.delete'],
        'inventory.read' => ['inventory.create'],        
        'inventory.update' => ['inventory.update'],
        'products.create' => ['products.create'],
        'products.delete' => ['products.delete'],
        'products.read' => ['products.read'],
        'products.update' => ['products.update'],
        'reports.read' => ['reports.read'],
        'role.create' => ['role.create'],
        'role.delete' => ['role.delete'],
        'role.update' => ['role.update'],
        'role.view' => ['role.view'],
        'sales.create' => ['sales.create'],
        'sales.delete' => ['sales.delete'],
        'sales.read' => ['sales.read'],
        'sales.update' => ['sales.update'],
        'users.create' => ['users.create'],
        'users.delete' => ['users.delete'],
        'users.read' => ['users.read'],
        'users.update' => ['users.update'],
    ];

    public static function getFeatureMap(): array
    {
        return self::$featureMap;
    }

    public function getFeaturePermissions(string $domain, string $action): array
    {
        $domainKey = $domain . '.' . $action;
        $requiredPermissions = [];

        // Cari di semua featureMap yang memiliki value domainKey
        foreach (self::$featureMap as $key => $permissions) {
            if (in_array($domainKey, $permissions)) {
                $requiredPermissions[] = $key;
            }    
        }

        if (empty($requiredPermissions)) {
            throw new \InvalidArgumentException("Invalid feature domain or action: {$domainKey}");
        }

        return $requiredPermissions;
    }

    public static function validatePermission(string $domain, string $action): bool
    {
        if (!isset(self::$featureMap[$domain][$action])) {
            throw new \InvalidArgumentException("Invalid feature domain or action: {$domain}.{$action}");
        }

        $requiredPermissions = self::$featureMap[$domain][$action];
        
        if (!Auth::check() || !Auth::user()->hasAnyPermission($requiredPermissions)) {
            throw new UnauthorizedException("Missing required permissions: " . implode(', ', $requiredPermissions));
        }

        return true;
    }

    public static function validateAnyPermission(array $permissions): bool
    {
        if (!Auth::check() || !Auth::user()->hasAnyPermission($permissions)) {
            throw new UnauthorizedException('Missing any of required permissions: ' . implode(', ', $permissions));
        }

        return true;
    }

    public static function validateAllPermissions(array $permissions): bool
    {
        if (!Auth::check() || !Auth::user()->hasAllPermissions($permissions)) {
            throw new UnauthorizedException('Missing some of required permissions: ' . implode(', ', $permissions));
        }

        return true;
    }
}
