<?php

declare(strict_types=1);

namespace App\Modules\Permissions\DTO;

readonly class RoleResponseDTO
{
    /**
     * @param  array<int, array{id: int, name: string}>  $permissions
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $guardName,
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
            'guard_name' => $this->guardName,
            'permissions' => $this->permissions,
        ];
    }
}
