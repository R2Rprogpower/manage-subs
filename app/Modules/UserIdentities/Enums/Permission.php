<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Enums;

enum Permission: string
{
    case MANAGE_USER_IDENTITIES = 'user_identities.manage';
    case VIEW_USER_IDENTITIES = 'user_identities.view';
    case CREATE_USER_IDENTITIES = 'user_identities.create';
    case UPDATE_USER_IDENTITIES = 'user_identities.update';
    case DELETE_USER_IDENTITIES = 'user_identities.delete';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
