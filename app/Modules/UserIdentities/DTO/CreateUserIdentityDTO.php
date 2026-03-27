<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\DTO;

readonly class CreateUserIdentityDTO
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public int $userId,
        public string $provider,
        public string $providerUserId,
        public ?string $username = null,
        public ?array $meta = null
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'provider' => $this->provider,
            'provider_user_id' => $this->providerUserId,
            'username' => $this->username,
            'meta' => $this->meta,
        ];
    }
}