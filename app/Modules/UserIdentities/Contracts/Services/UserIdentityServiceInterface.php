<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Contracts\Services;

use App\Models\UserIdentity;
use App\Modules\UserIdentities\DTO\CreateUserIdentityDTO;
use App\Modules\UserIdentities\DTO\UpdateUserIdentityDTO;
use Illuminate\Database\Eloquent\Collection;

interface UserIdentityServiceInterface
{
    /**
     * @return Collection<int, UserIdentity>
     */
    public function findAll(): Collection;

    public function findById(int $id): ?UserIdentity;

    public function create(CreateUserIdentityDTO $dto): UserIdentity;

    public function update(int $id, UpdateUserIdentityDTO $dto): UserIdentity;

    public function delete(int $id): void;
}
