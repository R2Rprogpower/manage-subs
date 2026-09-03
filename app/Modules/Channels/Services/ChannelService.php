<?php

declare(strict_types=1);

namespace App\Modules\Channels\Services;

use App\Models\TelegramChannel;
use App\Models\User;
use App\Modules\Channels\Contracts\Repositories\ChannelRepositoryInterface;
use App\Modules\Channels\Contracts\Services\ChannelServiceInterface;
use App\Modules\Channels\DTO\CreateChannelDTO;
use App\Modules\Channels\DTO\UpdateChannelDTO;
use Illuminate\Database\Eloquent\Collection;

class ChannelService implements ChannelServiceInterface
{
    public function __construct(private readonly ChannelRepositoryInterface $repository) {}

    public function findVisibleTo(User $user): Collection
    {
        return $this->repository->findVisibleTo($user);
    }

    public function findAvailable(): Collection
    {
        return $this->repository->findAvailable();
    }

    public function findById(int $id): ?TelegramChannel
    {
        return $this->repository->findById($id);
    }

    public function create(CreateChannelDTO $dto): TelegramChannel
    {
        if ($this->repository->findByTelegramIdentity($dto->telegramChatId, $dto->username)) {
            throw new \InvalidArgumentException('This Telegram channel is already registered.');
        }

        return $this->repository->create($dto);
    }

    public function update(int $id, UpdateChannelDTO $dto): TelegramChannel
    {
        $channel = $this->repository->findById($id);
        if (! $channel) {
            throw new \InvalidArgumentException("Telegram channel with ID {$id} not found.");
        }

        $this->repository->update($channel, $dto);

        return $channel->refresh()->load(['owner', 'plans']);
    }

    public function delete(int $id): void
    {
        $channel = $this->repository->findById($id);
        if (! $channel) {
            throw new \InvalidArgumentException("Telegram channel with ID {$id} not found.");
        }
        if ($channel->plans()->exists()) {
            throw new \InvalidArgumentException('A channel with subscription types cannot be deleted.');
        }

        $this->repository->delete($channel);
    }
}
