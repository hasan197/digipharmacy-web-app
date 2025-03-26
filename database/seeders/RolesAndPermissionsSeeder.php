<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator with full access']
        );

        $pharmacistRole = Role::firstOrCreate(
            ['name' => 'pharmacist'],
            ['description' => 'Pharmacist with medicine management access']
        );

        $cashierRole = Role::firstOrCreate(
            ['name' => 'cashier'],
            ['description' => 'Cashier with sales access']
        );

        // Create permissions for different modules
        $modules = [
            'users' => ['create', 'read', 'update', 'delete'],
            'products' => ['create', 'read', 'update', 'delete'],
            'sales' => ['create', 'read', 'update', 'delete'],
            'inventory' => ['create', 'read', 'update', 'delete'],
            'reports' => ['read'],
            'role' => ['view', 'create', 'update', 'delete'],
        ];

        $permissions = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[] = Permission::firstOrCreate(
                    ['name' => $module . '.' . $action],
                    [
                        'description' => ucfirst($action) . ' ' . ucfirst($module),
                        'module' => $module,
                        'action' => $action
                    ]
                );
            }
        }

        // Assign permissions to roles
        $adminRole->permissions()->sync(
            Permission::all()->pluck('id')->toArray()
        );

        $pharmacistRole->permissions()->sync(
            Permission::whereIn('module', ['products', 'inventory'])
                ->orWhere('name', 'reports.read')
                ->pluck('id')
                ->toArray()
        );

        $cashierRole->permissions()->sync(
            Permission::whereIn('name', [
                'sales.create',
                'sales.read',
                'products.read',
                'reports.read'
            ])->pluck('id')->toArray()
        );
    }
}
