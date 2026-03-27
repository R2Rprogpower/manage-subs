<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Contracts\Repositories;

use App\Models\UserIdentity;
use App\Modules\UserIdentities\DTO\CreateUserIdentityDTO;
use App\Modules\UserIdentities\DTO\UpdateUserIdentityDTO;
use Illuminate\Database\Eloquent\Collection;

interface UserIdentityRepositoryInterface
{
    public function findById(int $id): ?UserIdentity;

    /**
     * @return Collection<int, UserIdentity>
     */
    public function findAll(): Collection;

    public function findByProviderIdentity(string $provider, string $providerUserId): ?UserIdentity;

    public function create(CreateUserIdentityDTO $dto): UserIdentity;

    public function update(UserIdentity $userIdentity, UpdateUserIdentityDTO $dto): bool;

    public function delete(UserIdentity $userIdentity): bool;
}
