<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Policies;

use App\Models\User;
use App\Models\UserIdentity;
use App\Modules\UserIdentities\Enums\Permission as UserIdentityPermission;

class UserIdentityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(UserIdentityPermission::VIEW_USER_IDENTITIES->value);
    }

    public function view(User $user, UserIdentity $userIdentity): bool
    {
        return $user->can(UserIdentityPermission::VIEW_USER_IDENTITIES->value);
    }

    public function create(User $user): bool
    {
        return $user->can(UserIdentityPermission::CREATE_USER_IDENTITIES->value);
    }

    public function update(User $user, UserIdentity $userIdentity): bool
    {
        return $user->can(UserIdentityPermission::UPDATE_USER_IDENTITIES->value);
    }

    public function delete(User $user, UserIdentity $userIdentity): bool
    {
        return $user->can(UserIdentityPermission::DELETE_USER_IDENTITIES->value);
    }
}
