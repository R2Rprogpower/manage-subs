<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Services;

use App\Models\UserIdentity;
use App\Modules\UserIdentities\Contracts\Repositories\UserIdentityRepositoryInterface;
use App\Modules\UserIdentities\Contracts\Services\UserIdentityServiceInterface;
use App\Modules\UserIdentities\DTO\CreateUserIdentityDTO;
use App\Modules\UserIdentities\DTO\UpdateUserIdentityDTO;
use Illuminate\Database\Eloquent\Collection;

class UserIdentityService implements UserIdentityServiceInterface
{
    public function __construct(
        private readonly UserIdentityRepositoryInterface $userIdentityRepository
    ) {}

    /**
     * @return Collection<int, UserIdentity>
     */
    public function findAll(): Collection
    {
        return $this->userIdentityRepository->findAll();
    }

    public function findById(int $id): ?UserIdentity
    {
        return $this->userIdentityRepository->findById($id);
    }

    public function create(CreateUserIdentityDTO $dto): UserIdentity
    {
        $existing = $this->userIdentityRepository->findByProviderIdentity($dto->provider, $dto->providerUserId);
        if ($existing) {
            throw new \InvalidArgumentException('User identity already exists for the provider and provider user ID.');
        }

        return $this->userIdentityRepository->create($dto);
    }

    public function update(int $id, UpdateUserIdentityDTO $dto): UserIdentity
    {
        $userIdentity = $this->userIdentityRepository->findById($id);
        if (! $userIdentity) {
            throw new \InvalidArgumentException("User identity with ID {$id} not found.");
        }

        $payload = $dto->toArray();

        $provider = $payload['provider'] ?? $userIdentity->provider;
        $providerUserId = $payload['provider_user_id'] ?? $userIdentity->provider_user_id;

        $existing = $this->userIdentityRepository->findByProviderIdentity($provider, $providerUserId);
        if ($existing && $existing->id !== $userIdentity->id) {
            throw new \InvalidArgumentException('User identity already exists for the provider and provider user ID.');
        }

        $this->userIdentityRepository->update($userIdentity, $dto);
        $userIdentity->refresh();
        $userIdentity->load('user');

        return $userIdentity;
    }

    public function delete(int $id): void
    {
        $userIdentity = $this->userIdentityRepository->findById($id);
        if (! $userIdentity) {
            throw new \InvalidArgumentException("User identity with ID {$id} not found.");
        }

        $this->userIdentityRepository->delete($userIdentity);
    }
}
