<?php

declare(strict_types=1);

namespace App\Modules\Plans\DTO;

readonly class UpdatePlanDTO
{
    /**
     * @param  array<string, mixed>  $data
     */
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
