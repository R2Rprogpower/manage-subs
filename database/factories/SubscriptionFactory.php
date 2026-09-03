<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-30 days', 'now');
        $endsAt = fake()->boolean(80) ? fake()->dateTimeBetween($startedAt, '+1 year') : null;

        $status = fake()->randomElement(['draft', 'pending', 'active', 'suspended', 'expired', 'cancelled']);

        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'status' => $status,
            'started_at' => $startedAt,
            'activated_at' => $status === 'active' ? $startedAt : null,
            'suspended_at' => $status === 'suspended' ? now() : null,
            'cancelled_at' => $status === 'cancelled' ? now() : null,
            'ends_at' => $endsAt,
            'auto_renew' => fake()->boolean(),
            'trial_used' => fake()->boolean(),
            'source' => fake()->randomElement(['bot', 'admin', 'manual']),
        ];
    }
}
