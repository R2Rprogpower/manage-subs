<?php

declare(strict_types=1);

namespace App\Modules\Users\Repositories;

use App\Models\User;
use App\Modules\Users\DTO\CreateUserDTO;
use App\Modules\Users\DTO\UpdateUserDTO;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function findById(int $id): ?User
    {
        /** @var User|null */
        return User::query()
            ->with('roles', 'permissions')
            ->find($id);
    }

    /**
     * @return Collection<int, User>
     */
    public function findAll(): Collection
    {
        return User::with('roles', 'permissions')->get();
    }

    public function findByEmail(string $email): ?User
    {
        /** @var User|null */
        return User::query()
            ->where('email', $email)
            ->first();
    }

    public function create(CreateUserDTO $dto): User
    {
        /** @var User */
        $user = User::query()->create($dto->toArray());
        $user->load('roles', 'permissions');

        return $user;
    }

    public function update(User $user, UpdateUserDTO $dto): bool
    {
        return $user->update($dto->toArray());
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
