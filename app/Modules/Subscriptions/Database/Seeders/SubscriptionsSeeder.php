<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Database\Seeders;

use App\Modules\Subscriptions\Enums\Permission as SubscriptionPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SubscriptionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SubscriptionPermission::values() as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }
    }
}
