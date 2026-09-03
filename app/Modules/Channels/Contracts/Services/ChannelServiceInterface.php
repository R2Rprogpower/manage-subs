<?php

declare(strict_types=1);

namespace App\Modules\Channels\Contracts\Services;

use App\Models\TelegramChannel;
use App\Models\User;
use App\Modules\Channels\DTO\CreateChannelDTO;
use App\Modules\Channels\DTO\UpdateChannelDTO;
use Illuminate\Database\Eloquent\Collection;

interface ChannelServiceInterface
{
    /** @return Collection<int, TelegramChannel> */
    public function findVisibleTo(User $user): Collection;

    /** @return Collection<int, TelegramChannel> */
    public function findAvailable(): Collection;

    public function findById(int $id): ?TelegramChannel;

    public function create(CreateChannelDTO $dto): TelegramChannel;

    public function update(int $id, UpdateChannelDTO $dto): TelegramChannel;

    public function delete(int $id): void;
}
