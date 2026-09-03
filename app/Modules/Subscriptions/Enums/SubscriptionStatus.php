<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Enums;

enum SubscriptionStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, match ($this) {
            self::DRAFT => [self::PENDING, self::CANCELLED],
            self::PENDING => [self::ACTIVE, self::CANCELLED],
            self::ACTIVE => [self::ACTIVE, self::SUSPENDED, self::CANCELLED, self::EXPIRED],
            self::SUSPENDED => [self::ACTIVE, self::CANCELLED, self::EXPIRED],
            self::CANCELLED, self::EXPIRED => [self::ACTIVE],
        }, true);
    }
}
