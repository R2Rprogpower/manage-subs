<?php

declare(strict_types=1);

namespace App\Modules\Plans\Database\Seeders;

use App\Models\Plan;
use App\Models\TelegramChannel;
use App\Modules\Plans\Enums\Permission as PlanPermission;
use App\Modules\Plans\Enums\PlanKind;
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

        if (app()->environment('production') || ! config('telegram.test_data.enabled')) {
            return;
        }

        $channel = TelegramChannel::query()
            ->where('telegram_chat_id', (string) config('telegram.test_data.group.chat_id'))
            ->first();
        if ($channel === null) {
            return;
        }

        foreach ($this->defaultPlans($channel->id) as $plan) {
            Plan::query()->updateOrCreate(
                ['code' => $plan['code']],
                $plan
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultPlans(int $channelId): array
    {
        return [
            [
                'telegram_channel_id' => $channelId,
                'code' => 'free',
                'name' => 'Free',
                'kind' => PlanKind::MONEY->value,
                'price_minor' => 0,
                'currency' => 'USD',
                'duration_days' => null,
                'configuration' => null,
                'is_active' => true,
            ],
            [
                'telegram_channel_id' => $channelId,
                'code' => 'trial_7_days',
                'name' => 'Trial 7 Days',
                'kind' => PlanKind::MONEY->value,
                'price_minor' => 0,
                'currency' => 'USD',
                'duration_days' => 7,
                'configuration' => null,
                'is_active' => true,
            ],
            [
                'telegram_channel_id' => $channelId,
                'code' => 'monthly',
                'name' => 'Monthly',
                'kind' => PlanKind::MONEY->value,
                'price_minor' => 999,
                'currency' => 'USD',
                'duration_days' => 30,
                'configuration' => null,
                'is_active' => true,
            ],
            [
                'telegram_channel_id' => $channelId,
                'code' => 'yearly',
                'name' => 'Yearly',
                'kind' => PlanKind::MONEY->value,
                'price_minor' => 9999,
                'currency' => 'USD',
                'duration_days' => 365,
                'configuration' => null,
                'is_active' => true,
            ],
        ];
    }
}
