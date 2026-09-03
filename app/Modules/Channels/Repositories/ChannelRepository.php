<?php

declare(strict_types=1);

namespace App\Modules\Channels\Repositories;

use App\Models\TelegramChannel;
use App\Models\User;
use App\Modules\Channels\Contracts\Repositories\ChannelRepositoryInterface;
use App\Modules\Channels\DTO\CreateChannelDTO;
use App\Modules\Channels\DTO\UpdateChannelDTO;
use App\Modules\Channels\Enums\ChannelStatus;
use App\Modules\Channels\Enums\Permission as ChannelPermission;
use Illuminate\Database\Eloquent\Collection;

class ChannelRepository implements ChannelRepositoryInterface
{
    public function findById(int $id): ?TelegramChannel
    {
        /** @var TelegramChannel|null */
        return TelegramChannel::query()->with(['owner', 'plans'])->find($id);
    }

    public function findByTelegramIdentity(string $telegramChatId, ?string $username = null): ?TelegramChannel
    {
        /** @var TelegramChannel|null */
        return TelegramChannel::query()
            ->where('telegram_chat_id', $telegramChatId)
            ->when($username !== null, fn ($query) => $query->orWhere('username', $username))
            ->first();
    }

    public function findVisibleTo(User $user): Collection
    {
        return TelegramChannel::query()
            ->with(['owner', 'plans'])
            ->when(
                ! $user->can(ChannelPermission::VIEW_CHANNELS->value),
                fn ($query) => $query->where('owner_id', $user->id)
            )
            ->orderBy('title')
            ->get();
    }

    public function findAvailable(): Collection
    {
        return TelegramChannel::query()
            ->with(['plans' => fn ($query) => $query->where('is_active', true)->orderBy('price_minor')])
            ->where('status', ChannelStatus::ACTIVE->value)
            ->whereHas('plans', fn ($query) => $query->where('is_active', true))
            ->orderBy('title')
            ->get();
    }

    public function create(CreateChannelDTO $dto): TelegramChannel
    {
        /** @var TelegramChannel $channel */
        $channel = TelegramChannel::query()->create($dto->toArray());

        return $channel->load(['owner', 'plans']);
    }

    public function update(TelegramChannel $channel, UpdateChannelDTO $dto): bool
    {
        return $channel->update($dto->toArray());
    }

    public function delete(TelegramChannel $channel): bool
    {
        return $channel->delete();
    }
}
