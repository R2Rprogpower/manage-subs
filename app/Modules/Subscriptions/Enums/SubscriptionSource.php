<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Enums;

enum SubscriptionSource: string
{
    case BOT = 'bot';
    case ADMIN = 'admin';
    case MANUAL = 'manual';
    case PLACEHOLDER = 'placeholder';

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
