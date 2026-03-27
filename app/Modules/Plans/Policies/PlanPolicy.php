<?php

declare(strict_types=1);

namespace App\Modules\Plans\Policies;

use App\Models\Plan;
use App\Models\User;
use App\Modules\Plans\Enums\Permission as PlanPermission;

class PlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PlanPermission::VIEW_PLANS->value);
    }

    public function view(User $user, Plan $plan): bool
    {
        return $user->can(PlanPermission::VIEW_PLANS->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PlanPermission::CREATE_PLANS->value);
    }

    public function update(User $user, Plan $plan): bool
    {
        return $user->can(PlanPermission::UPDATE_PLANS->value);
    }

    public function delete(User $user, Plan $plan): bool
    {
        return $user->can(PlanPermission::DELETE_PLANS->value);
    }
}
