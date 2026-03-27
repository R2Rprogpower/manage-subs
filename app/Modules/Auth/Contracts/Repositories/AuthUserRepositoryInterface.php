<?php

declare(strict_types=1);

namespace App\Modules\Auth\Contracts\Repositories;

use App\Models\User;

interface AuthUserRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function save(User $user): void;
}
