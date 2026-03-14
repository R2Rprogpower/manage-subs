<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Policies;

use App\Models\User;
use App\Modules\Permissions\Enums\Permission as PermissionEnum;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::MANAGE_PERMISSIONS->value);
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->can(PermissionEnum::MANAGE_PERMISSIONS->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::MANAGE_PERMISSIONS->value);
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->can(PermissionEnum::MANAGE_PERMISSIONS->value);
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->can(PermissionEnum::MANAGE_PERMISSIONS->value);
    }
}
