<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserIdentity>
 */
class UserIdentityFactory extends Factory
{
    protected $model = UserIdentity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => fake()->randomElement(['telegram', 'google', 'github']),
            'provider_user_id' => fake()->unique()->numerify('provider-########'),
            'username' => fake()->optional()->userName(),
            'meta' => [
                'locale' => fake()->languageCode(),
            ],
        ];
    }
}
