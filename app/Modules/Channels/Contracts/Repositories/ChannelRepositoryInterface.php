<?php

declare(strict_types=1);

namespace App\Modules\Channels\Contracts\Repositories;

use App\Models\TelegramChannel;
use App\Models\User;
use App\Modules\Channels\DTO\CreateChannelDTO;
use App\Modules\Channels\DTO\UpdateChannelDTO;
use Illuminate\Database\Eloquent\Collection;

interface ChannelRepositoryInterface
{
    public function findById(int $id): ?TelegramChannel;

    public function findByTelegramIdentity(string $telegramChatId, ?string $username = null): ?TelegramChannel;

    /** @return Collection<int, TelegramChannel> */
    public function findVisibleTo(User $user): Collection;

    /** @return Collection<int, TelegramChannel> */
    public function findAvailable(): Collection;

    public function create(CreateChannelDTO $dto): TelegramChannel;

    public function update(TelegramChannel $channel, UpdateChannelDTO $dto): bool;

    public function delete(TelegramChannel $channel): bool;
}
