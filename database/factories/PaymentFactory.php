<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'provider' => fake()->randomElement(['telegram_stars', 'manual', 'stripe']),
            'provider_payment_id' => fake()->optional()->uuid(),
            'status' => fake()->randomElement(['pending', 'paid', 'failed']),
            'amount_minor' => fake()->numberBetween(0, 9999),
            'currency' => 'USD',
            'paid_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}