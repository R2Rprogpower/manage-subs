<?php

declare(strict_types=1);

namespace App\Modules\Plans\Database\Seeders;

use App\Models\Plan;
use App\Modules\Plans\Enums\Permission as PlanPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PlanPermission::values() as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        foreach ($this->defaultPlans() as $plan) {
            Plan::query()->updateOrCreate(
                ['code' => $plan['code']],
                $plan
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultPlans(): array
    {
        return [
            [
                'code' => 'free',
                'name' => 'Free',
                'price_minor' => 0,
                'currency' => 'USD',
                'duration_days' => null,
                'is_active' => true,
            ],
            [
                'code' => 'trial_7_days',
                'name' => 'Trial 7 Days',
                'price_minor' => 0,
                'currency' => 'USD',
                'duration_days' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'monthly',
                'name' => 'Monthly',
                'price_minor' => 999,
                'currency' => 'USD',
                'duration_days' => 30,
                'is_active' => true,
            ],
            [
                'code' => 'yearly',
                'name' => 'Yearly',
                'price_minor' => 9999,
                'currency' => 'USD',
                'duration_days' => 365,
                'is_active' => true,
            ],
        ];
    }
}