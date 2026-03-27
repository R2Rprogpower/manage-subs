<?php

declare(strict_types=1);

namespace App\Modules\Plans\Enums;

enum Permission: string
{
    case MANAGE_PLANS = 'plans.manage';
    case VIEW_PLANS = 'plans.view';
    case CREATE_PLANS = 'plans.create';
    case UPDATE_PLANS = 'plans.update';
    case DELETE_PLANS = 'plans.delete';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
