<?php

declare(strict_types=1);

namespace App\Modules\Permissions\DTO;

readonly class CreateRoleDTO
{
    public function __construct(
        public string $name,
        public string $guardName = 'web'
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'guard_name' => $this->guardName,
        ];
    }
}
