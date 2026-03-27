<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Policies;

use App\Models\Subscription;
use App\Models\User;
use App\Modules\Subscriptions\Enums\Permission as SubscriptionPermission;

class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(SubscriptionPermission::VIEW_SUBSCRIPTIONS->value);
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $user->can(SubscriptionPermission::VIEW_SUBSCRIPTIONS->value);
    }

    public function create(User $user): bool
    {
        return $user->can(SubscriptionPermission::CREATE_SUBSCRIPTIONS->value);
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $user->can(SubscriptionPermission::UPDATE_SUBSCRIPTIONS->value);
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->can(SubscriptionPermission::DELETE_SUBSCRIPTIONS->value);
    }
}
