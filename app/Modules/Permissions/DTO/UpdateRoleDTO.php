<?php

declare(strict_types=1);

namespace App\Modules\Permissions\DTO;

readonly class UpdateRoleDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $guardName = null
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->guardName !== null) {
            $data['guard_name'] = $this->guardName;
        }

        return $data;
    }
}
