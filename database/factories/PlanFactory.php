<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use App\Models\TelegramChannel;
use App\Modules\Plans\Enums\PlanKind;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'telegram_channel_id' => TelegramChannel::factory(),
            'code' => Str::of($name)->lower()->replace(' ', '_')->value(),
            'name' => Str::of($name)->title()->value(),
            'kind' => PlanKind::MONEY->value,
            'price_minor' => fake()->numberBetween(0, 4999),
            'currency' => 'USD',
            'duration_days' => fake()->randomElement([7, 30, 365]),
            'configuration' => null,
            'is_active' => true,
        ];
    }
}
