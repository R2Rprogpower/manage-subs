<?php

declare(strict_types=1);

namespace App\Modules\Channels\Processors;

use App\Core\Abstracts\Processor;
use App\Models\TelegramChannel;
use App\Models\User;
use App\Modules\Channels\Contracts\Services\ChannelServiceInterface;
use App\Modules\Channels\DTO\CreateChannelDTO;
use App\Modules\Channels\Enums\ChannelStatus;
use App\Modules\Channels\Http\Requests\StoreChannelRequest;

class ChannelStoreProcessor extends Processor
{
    public function __construct(private readonly ChannelServiceInterface $service) {}

    public function execute(StoreChannelRequest $request): TelegramChannel
    {
        $data = $request->validated();
        $user = $request->user();
        if (! $user instanceof User) {
            throw new \LogicException('Authenticated user is required.');
        }

        return $this->service->create(new CreateChannelDTO(
            ownerId: $user->id,
            telegramChatId: $data['telegram_chat_id'],
            username: isset($data['username']) ? ltrim($data['username'], '@') : null,
            title: $data['title'],
            description: $data['description'] ?? null,
            status: $data['status'] ?? ChannelStatus::DRAFT->value,
        ));
    }
}
