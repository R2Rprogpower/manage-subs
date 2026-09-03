<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TelegramChannel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TelegramChannel> */
class TelegramChannelFactory extends Factory
{
    protected $model = TelegramChannel::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $username = fake()->unique()->userName();

        return [
            'owner_id' => User::factory(),
            'telegram_chat_id' => (string) fake()->unique()->numberBetween(100000, 999999999),
            'username' => $username,
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
