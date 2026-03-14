<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Policies;

use App\Models\User;
use App\Modules\Permissions\Enums\Permission as PermissionEnum;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::MANAGE_ROLES->value);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can(PermissionEnum::MANAGE_ROLES->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::MANAGE_ROLES->value);
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can(PermissionEnum::MANAGE_ROLES->value);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can(PermissionEnum::MANAGE_ROLES->value);
    }
}
