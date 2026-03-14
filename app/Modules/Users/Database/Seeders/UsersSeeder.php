<?php

declare(strict_types=1);

namespace App\Modules\Users\Database\Seeders;

use App\Models\User;
use App\Modules\Permissions\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'super-admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password123',
            ]
        );
        $superAdmin->syncRoles([RoleEnum::SUPER_ADMIN->value]);

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password123',
            ]
        );
        $admin->syncRoles([RoleEnum::ADMIN->value]);

        $agent = User::query()->firstOrCreate(
            ['email' => 'agent@example.com'],
            [
                'name' => 'Agent User',
                'password' => 'password123',
            ]
        );
        $agent->syncRoles([RoleEnum::AGENT->value]);

        User::factory(3)->create()->each(function (User $user): void {
            $user->syncRoles([RoleEnum::GUEST->value]);
        });
    }
}
