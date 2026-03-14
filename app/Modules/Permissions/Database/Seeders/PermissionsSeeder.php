<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Database\Seeders;

use App\Modules\Permissions\Enums\Permission as PermissionEnum;
use App\Modules\Permissions\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionEnum::values() as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        foreach (RoleEnum::values() as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        $allPermissionNames = PermissionEnum::values();

        Role::query()->where('name', RoleEnum::SUPER_ADMIN->value)->first()?->syncPermissions($allPermissionNames);
        Role::query()->where('name', RoleEnum::ADMIN->value)->first()?->syncPermissions($allPermissionNames);

        Role::query()->where('name', RoleEnum::AGENT->value)->first()?->syncPermissions([
            PermissionEnum::VIEW_USERS->value,
            PermissionEnum::VIEW_ROLES->value,
            PermissionEnum::VIEW_PERMISSIONS->value,
        ]);

        Role::query()->where('name', RoleEnum::GUEST->value)->first()?->syncPermissions([]);
    }
}
