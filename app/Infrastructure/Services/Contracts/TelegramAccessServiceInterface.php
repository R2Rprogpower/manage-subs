<?php

declare(strict_types=1);

namespace App\Infrastructure\Services\Contracts;

interface TelegramAccessServiceInterface
{
    public function grantChannelAccess(int $userId, int $channelId): bool;

    public function revokeChannelAccess(int $userId, int $channelId): bool;

    public function syncUserAccess(int $userId, int $channelId, bool $shouldHaveAccess): bool;
}
