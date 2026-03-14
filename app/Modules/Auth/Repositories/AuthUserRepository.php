<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Models\User;

class AuthUserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', $email)
            ->first();
    }

    public function save(User $user): void
    {
        $user->save();
    }
}
