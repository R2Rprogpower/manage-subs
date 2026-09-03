<?php

declare(strict_types=1);

namespace App\Modules\Channels\Enums;

enum Permission: string
{
    case MANAGE_CHANNELS = 'channels.manage';
    case VIEW_CHANNELS = 'channels.view';
    case UPDATE_CHANNELS = 'channels.update';
    case DELETE_CHANNELS = 'channels.delete';

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
