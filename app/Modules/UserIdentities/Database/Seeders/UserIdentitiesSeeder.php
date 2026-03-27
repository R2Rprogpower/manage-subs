<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Database\Seeders;

use App\Modules\UserIdentities\Enums\Permission as UserIdentityPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class UserIdentitiesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (UserIdentityPermission::values() as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }
    }
}