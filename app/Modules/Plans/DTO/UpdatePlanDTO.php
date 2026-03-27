<?php

declare(strict_types=1);

namespace App\Modules\Plans\DTO;

readonly class UpdatePlanDTO
{
    public function __construct(
        private array $data = []
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}