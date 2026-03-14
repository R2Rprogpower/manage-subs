<?php

declare(strict_types=1);

namespace App\Core\Interfaces;

interface PresentationInterface
{
    /**
     * Format data for API response (associative or list).
     *
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array;
}
