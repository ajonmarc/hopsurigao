<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'users.view', 'label' => 'View Users', 'group' => 'users'],
            ['name' => 'users.create', 'label' => 'Create Users', 'group' => 'users'],
            ['name' => 'users.edit', 'label' => 'Edit Users', 'group' => 'users'],
            ['name' => 'users.delete', 'label' => 'Delete Users', 'group' => 'users'],

            ['name' => 'roles.view', 'label' => 'View Roles', 'group' => 'roles'],
            ['name' => 'roles.create', 'label' => 'Create Roles', 'group' => 'roles'],
            ['name' => 'roles.edit', 'label' => 'Edit Roles', 'group' => 'roles'],
            ['name' => 'roles.delete', 'label' => 'Delete Roles', 'group' => 'roles'],

            ['name' => 'permissions.view', 'label' => 'View Permissions', 'group' => 'permissions'],
            ['name' => 'permissions.assign', 'label' => 'Assign Permissions', 'group' => 'permissions'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}