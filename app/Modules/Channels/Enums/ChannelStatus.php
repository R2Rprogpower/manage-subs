<?php

declare(strict_types=1);

namespace App\Modules\Channels\Enums;

enum ChannelStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case UNAVAILABLE = 'unavailable';

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
