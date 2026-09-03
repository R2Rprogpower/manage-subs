<?php

declare(strict_types=1);

namespace App\Modules\Channels\DTO;

readonly class CreateChannelDTO
{
    public function __construct(
        public int $ownerId,
        public string $telegramChatId,
        public ?string $username,
        public string $title,
        public ?string $description,
        public string $status,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'owner_id' => $this->ownerId,
            'telegram_chat_id' => $this->telegramChatId,
            'username' => $this->username,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
