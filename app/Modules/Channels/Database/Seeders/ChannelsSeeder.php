<?php

declare(strict_types=1);

namespace App\Modules\Channels\Database\Seeders;

use App\Modules\Channels\Enums\Permission as ChannelPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ChannelsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ChannelPermission::values() as $permissionName) {
            Permission::query()->firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }
    }
}
