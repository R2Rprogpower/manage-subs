<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Repositories;

use App\Models\UserIdentity;
use App\Modules\UserIdentities\DTO\CreateUserIdentityDTO;
use App\Modules\UserIdentities\DTO\UpdateUserIdentityDTO;
use Illuminate\Database\Eloquent\Collection;

class UserIdentityRepository
{
    public function findById(int $id): ?UserIdentity
    {
        /** @var UserIdentity|null */
        return UserIdentity::query()
            ->with('user')
            ->find($id);
    }

    /**
     * @return Collection<int, UserIdentity>
     */
    public function findAll(): Collection
    {
        return UserIdentity::query()
            ->with('user')
            ->get();
    }

    public function findByProviderIdentity(string $provider, string $providerUserId): ?UserIdentity
    {
        /** @var UserIdentity|null */
        return UserIdentity::query()
            ->where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();
    }

    public function create(CreateUserIdentityDTO $dto): UserIdentity
    {
        /** @var UserIdentity $userIdentity */
        $userIdentity = UserIdentity::query()->create($dto->toArray());
        $userIdentity->load('user');

        return $userIdentity;
    }

    public function update(UserIdentity $userIdentity, UpdateUserIdentityDTO $dto): bool
    {
        return $userIdentity->update($dto->toArray());
    }

    public function delete(UserIdentity $userIdentity): bool
    {
        return $userIdentity->delete();
    }
}