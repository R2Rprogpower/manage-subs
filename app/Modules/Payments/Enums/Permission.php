<?php

declare(strict_types=1);

namespace App\Modules\Payments\Enums;

enum Permission: string
{
    case MANAGE_PAYMENTS = 'payments.manage';
    case VIEW_PAYMENTS = 'payments.view';
    case CREATE_PAYMENTS = 'payments.create';
    case UPDATE_PAYMENTS = 'payments.update';
    case DELETE_PAYMENTS = 'payments.delete';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
