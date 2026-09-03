<?php

namespace Database\Seeders;

use App\Modules\Permissions\Enums\Role as RoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $moduleSeeders = [];

        foreach (glob(app_path('Modules/*/Database/Seeders/*Seeder.php')) ?: [] as $path) {
            if (! preg_match('#Modules/([^/]+)/Database/Seeders/([^/]+)\.php$#', str_replace('\\', '/', $path), $matches)) {
                continue;
            }

            $moduleSeeders[] = sprintf(
                'App\\Modules\\%s\\Database\\Seeders\\%s',
                $matches[1],
                $matches[2]
            );
        }

        sort($moduleSeeders);

        if ($moduleSeeders !== []) {
            $this->call($moduleSeeders);
        }

        $allPermissions = Permission::query()->pluck('name')->all();
        Role::query()->whereIn('name', [RoleEnum::SUPER_ADMIN->value, RoleEnum::ADMIN->value])
            ->get()
            ->each(fn (Role $role) => $role->syncPermissions($allPermissions));
    }
}
