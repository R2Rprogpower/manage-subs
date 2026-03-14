<?php

declare(strict_types=1);

namespace App\Modules\Users\Policies;

use App\Models\User;
use App\Modules\Users\Enums\Permission as UserPermission;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(UserPermission::VIEW_USERS->value);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(UserPermission::VIEW_USERS->value);
    }

    public function create(User $user): bool
    {
        return $user->can(UserPermission::CREATE_USERS->value);
    }

    public function update(User $user, User $model): bool
    {
        return $user->can(UserPermission::UPDATE_USERS->value);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can(UserPermission::DELETE_USERS->value);
    }
}
