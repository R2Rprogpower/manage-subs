<?php

declare(strict_types=1);

namespace App\Modules\Users\Enums;

enum Permission: string
{
    case MANAGE_USERS = 'users.manage';
    case VIEW_USERS = 'users.view';
    case CREATE_USERS = 'users.create';
    case UPDATE_USERS = 'users.update';
    case DELETE_USERS = 'users.delete';

    /**
     * Get all permission values as array
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get permission names as array
     *
     * @return array<string>
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
