<?php

declare(strict_types=1);

namespace App\Modules\Users\Contracts\Repositories;

use App\Models\User;
use App\Modules\Users\DTO\CreateUserDTO;
use App\Modules\Users\DTO\UpdateUserDTO;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    /**
     * @return Collection<int, User>
     */
    public function findAll(): Collection;

    public function findByEmail(string $email): ?User;

    public function create(CreateUserDTO $dto): User;

    public function update(User $user, UpdateUserDTO $dto): bool;

    public function delete(User $user): bool;
}