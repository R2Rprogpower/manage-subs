<?php

declare(strict_types=1);

namespace App\Modules\Payments\Database\Seeders;

use App\Modules\Payments\Enums\Permission as PaymentPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PaymentsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PaymentPermission::values() as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }
    }
}
