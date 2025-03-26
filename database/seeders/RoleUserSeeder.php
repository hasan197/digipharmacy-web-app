<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        // Get all roles
        $adminRole = Role::where('name', 'admin')->first();
        $pharmacistRole = Role::where('name', 'pharmacist')->first();
        $cashierRole = Role::where('name', 'cashier')->first();

        // Assign admin role to first user
        $adminUser = User::where('email', 'admin@digipharmacy.com')->first();
        if ($adminUser) {
            $adminUser->roles()->sync([$adminRole->id]);
        }

        // Assign pharmacist role to second user
        $pharmacistUser = User::where('email', 'pharmacist@digipharmacy.com')->first();
        if ($pharmacistUser) {
            $pharmacistUser->roles()->sync([$pharmacistRole->id]);
        }

        // Assign cashier role to third user
        $cashierUser = User::where('email', 'cashier@digipharmacy.com')->first();
        if ($cashierUser) {
            $cashierUser->roles()->sync([$cashierRole->id]);
        }
    }
}
