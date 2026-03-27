<?php

declare(strict_types=1);

namespace App\Modules\Users\Contracts\Services;

use App\Models\User;
use App\Modules\Users\DTO\CreateUserDTO;
use App\Modules\Users\DTO\UpdateUserDTO;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface UserServiceInterface
{
    /**
     * @return Collection<int, User>
     */
    public function findAll(): Collection;

    public function findById(int $id): ?User;

    public function create(CreateUserDTO $dto, ?User $actor = null, ?Request $request = null): User;

    public function update(int $id, UpdateUserDTO $dto, ?User $actor = null, ?Request $request = null): User;

    public function delete(int $id, ?User $actor = null, ?Request $request = null): void;
}