<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            ['name' => 'View Products', 'slug' => 'view-products', 'description' => 'Can view products'],
            ['name' => 'Create Products', 'slug' => 'create-products', 'description' => 'Can create new products'],
            ['name' => 'Edit Products', 'slug' => 'edit-products', 'description' => 'Can edit existing products'],
            ['name' => 'Delete Products', 'slug' => 'delete-products', 'description' => 'Can delete products'],
            ['name' => 'View Orders', 'slug' => 'view-orders', 'description' => 'Can view orders'],
            ['name' => 'Manage Orders', 'slug' => 'manage-orders', 'description' => 'Can manage orders'],
            ['name' => 'View Users', 'slug' => 'view-users', 'description' => 'Can view users'],
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'description' => 'Can manage users'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }

        // Create Roles
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator', 'description' => 'Full system access']
        );

        $managerRole = Role::firstOrCreate(
            ['slug' => 'manager'],
            ['name' => 'Manager', 'description' => 'Can manage products and orders']
        );

        $customerRole = Role::firstOrCreate(
            ['slug' => 'customer'],
            ['name' => 'Customer', 'description' => 'Regular customer access']
        );

        // Assign Permissions to Roles
        $adminRole->permissions()->sync(Permission::all());

        $managerRole->permissions()->sync(
            Permission::whereIn('slug', [
                'view-products',
                'create-products',
                'edit-products',
                'view-orders',
                'manage-orders',
            ])->get()
        );

        $customerRole->permissions()->sync(
            Permission::whereIn('slug', [
                'view-products',
                'view-orders',
            ])->get()
        );
    }
}
