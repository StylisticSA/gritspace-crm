<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $models = [
            'closed offices', 'dedicated desks', 'amenities', 'boardrooms', 'categories', 'hot desks',
            'virtual offices', 'locations', 'roles', 'permissions', 'users', 'agreements', 'client details', 
            'extras', 'parking', 'discounts', 'invoices', 'notes'
        ];
        
        $actions = ['create', 'edit', 'delete', 'view'];
        
        foreach ($models as $model) {
            foreach ($actions as $action) {
                Permission::findOrCreate("{$action} {$model}", 'web');
            }
        }

        $customPermissions = [
            'manage settings', 'view book offices', 'view book boardrooms', 
            'view book extras', 'view dashboard', 'menu offices', 'view book virtuals', 'view enquire'
        ];

        foreach ($customPermissions as $permName) {
            Permission::findOrCreate($permName, 'web');
        }

        $superAdmin = Role::findOrCreate('super admin', 'web');
        $superAdmin->syncPermissions(Permission::all());

        $this->command->info("Permissions synced successfully to the live server.");
    }
}
