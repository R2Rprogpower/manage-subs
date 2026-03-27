<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Enums;

enum Permission: string
{
    case MANAGE_SUBSCRIPTIONS = 'subscriptions.manage';
    case VIEW_SUBSCRIPTIONS = 'subscriptions.view';
    case CREATE_SUBSCRIPTIONS = 'subscriptions.create';
    case UPDATE_SUBSCRIPTIONS = 'subscriptions.update';
    case DELETE_SUBSCRIPTIONS = 'subscriptions.delete';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
