<?php

declare(strict_types=1);

namespace App\Modules\Plans\Enums;

enum PlanKind: string
{
    case MONEY = 'money';
    case ACHIEVEMENT = 'achievement';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
