<?php

declare(strict_types=1);

namespace App\Modules\Payments\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Modules\Payments\Enums\Permission as PaymentPermission;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PaymentPermission::VIEW_PAYMENTS->value);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->can(PaymentPermission::VIEW_PAYMENTS->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PaymentPermission::CREATE_PAYMENTS->value);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->can(PaymentPermission::UPDATE_PAYMENTS->value);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->can(PaymentPermission::DELETE_PAYMENTS->value);
    }
}
