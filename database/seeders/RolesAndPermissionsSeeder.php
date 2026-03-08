<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        if (Role::count() || Permission::count()) {
            echo "Roles or permissions already exist. Skipping seeder.\n";
            return;
        }

        // Define roles
        $roles = ['Super Admin', 'Admin','User', 'Pending User'];
        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName]);
        }

        // Define models and actions
        $models = [
            'offices', 'amenities', 'boardrooms', 'categories',
            'help desks', 'office pricing', 'virtual offices',
            'locations', 'office rates'
        ];
        $actions = ['create', 'edit', 'delete', 'view'];

        $allPermissions = [];

        // Create model-based CRUD permissions
        foreach ($models as $model) {
            foreach ($actions as $action) {
                $perm = Permission::create(['name' => "{$action} {$model}"]);
                $allPermissions[] = $perm->id;
            }
        }

        // Custom permissions (menu/UI logic)
        $customPermissions = [
            'manage settings',
            'view book offices',
            'view book boardrooms',
            'view book extras',
            'add users',
            'add roles',
            'add permissions',
            'edit bookings',
            'delete bookings',
            'View dashboard'
        ];

        //permissions on users - view book boardrooms view book extrasview book offices
        foreach ($customPermissions as $permName) {
            $perm = Permission::create(['name' => $permName]);
            $allPermissions[] = $perm->id;
        }

        // Assign all permissions to Super Admin
        $superAdmin = Role::where('name', 'Super Admin')->first();
        $superAdmin->permissions()->sync($allPermissions);

        // Assign Super Admin role to the default user
        $user = User::firstOrCreate(['email' => 'admin@admin.com'], [
            'name' => 'Super Admin',
            'password' => bcrypt('superadmin'),
        ]);
        $user->roles()->syncWithoutDetaching([$superAdmin->id]);


        echo "Roles and permissions seeded successfully.\n";
    }
}
