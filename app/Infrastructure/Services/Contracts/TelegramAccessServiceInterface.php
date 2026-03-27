<?php

declare(strict_types=1);

namespace App\Infrastructure\Services\Contracts;

interface TelegramAccessServiceInterface
{
    public function grantChannelAccess(int $userId): bool;

    public function revokeChannelAccess(int $userId): bool;

    public function syncUserAccess(int $userId, bool $shouldHaveAccess): bool;
}
