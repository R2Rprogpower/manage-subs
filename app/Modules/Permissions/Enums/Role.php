<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Enums;

enum Role: string
{
    case SUPER_ADMIN = 'super-admin';
    case ADMIN = 'admin';
    case AGENT = 'agent';
    case GUEST = 'guest';

    /**
     * Get all role values as array
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get role names as array
     *
     * @return array<string>
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
