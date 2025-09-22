<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        echo "RoleSeeder starting...\n";
        
        // Define permissions
        $permissions = [
            'view project',
            'edit project',
            'delete project',
            'create project',
            'view page',
            'edit page',
            'delete page',
            'create page',
        ];

        foreach ($permissions as $permission) {
            $perm = Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'sanctum']
            );
            //echo "Permission created: {$perm->name} for guard: {$perm->guard_name}\n";
        }

        // Define roles and assign permissions
        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'sanctum']);
        echo "Role created: {$owner->name} for guard: {$owner->guard_name}\n";
        $owner->syncPermissions($permissions);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        $admin->syncPermissions([
            'view project',
            'edit project',
            'create project',
            'view page',
            'edit page',
            'create page',
        ]);

        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'sanctum']);
        $editor->syncPermissions([
            'view project',
            'edit project',
            'view page',
            'edit page',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'sanctum']);
        $viewer->syncPermissions([
            'view project',
            'view page',
        ]);
    }

}
