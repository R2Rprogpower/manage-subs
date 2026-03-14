<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'ip_address' => fake()->ipv4(),
            'action_type' => fake()->randomElement([
                'auth_login',
                'auth_logout',
                'user_created',
                'role_assigned',
                'permission_assigned_to_user',
            ]),
            'target_type' => fake()->randomElement(['user', 'role', 'permission']),
            'target_id' => fake()->numberBetween(1, 1000),
            'previous_value' => null,
            'new_value' => null,
            'metadata' => null,
            'created_at' => now(),
        ];
    }
}
