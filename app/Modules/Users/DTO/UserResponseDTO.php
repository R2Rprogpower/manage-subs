<?php

declare(strict_types=1);

namespace App\Modules\Users\DTO;

use App\Models\User;

readonly class UserResponseDTO
{
    /**
     * @param  array<string>  $roles
     * @param  array<string>  $permissions
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $emailVerifiedAt = null,
        public array $roles = [],
        public array $permissions = []
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->emailVerifiedAt,
            'roles' => $this->roles,
            'permissions' => $this->permissions,
        ];
    }

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            emailVerifiedAt: $user->email_verified_at?->toIso8601String(),
            roles: $user->getRoleNames()->toArray(),
            permissions: $user->getAllPermissions()->pluck('name')->toArray()
        );
    }
}
