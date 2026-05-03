<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions (Spatie specific)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Roles
        $roles = ['super admin', 'admin', 'user', 'pending user'];
        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        $models = ['closed offices','dedicated desks','amenities', 'boardrooms', 'categories', 'hot desks',
                   'virtual offices', 'locations', 'roles', 'permissions','users', 'agreements','client details', 
                   'extras', 'parking', 'discounts', 'invoices','notes'];
        
        $actions = ['create', 'edit', 'delete', 'view'];
        
        foreach ($models as $model) {
            foreach ($actions as $action) {
                Permission::findOrCreate("{$action} {$model}", 'web');
            }
        }

        $customPermissions = [
            'manage settings', 'view book offices', 'view book boardrooms', 
            'view book extras', 'view dashboard'
        ];

        foreach ($customPermissions as $permName) {
            Permission::findOrCreate($permName, 'web');
        }

        $superAdmin = Role::findByName('Super Admin');
        $superAdmin->syncPermissions(Permission::all());

        $user = User::firstOrCreate(['email' => 'admin@admin.com'], [
            'name' => 'Super Admin',
            'password' => bcrypt('superadmin'),
        ]);
        
        $user->assignRole($superAdmin);

        $this->command->info("Roles and permissions seeded successfully.");
    }
}
