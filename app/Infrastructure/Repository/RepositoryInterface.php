<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

interface RepositoryInterface
{
    public function findById(int $id): ?object;

    /**
     * @return array<int, object>
     */
    public function findAll(): array;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): object;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
