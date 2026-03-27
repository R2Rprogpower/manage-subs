<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Services;

use App\Models\Subscription;
use App\Modules\Subscriptions\Contracts\Repositories\SubscriptionRepositoryInterface;
use App\Modules\Subscriptions\Contracts\Services\SubscriptionServiceInterface;
use App\Modules\Subscriptions\DTO\CreateSubscriptionDTO;
use App\Modules\Subscriptions\DTO\UpdateSubscriptionDTO;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionService implements SubscriptionServiceInterface
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository
    ) {}

    /**
     * @return Collection<int, Subscription>
     */
    public function findAll(): Collection
    {
        return $this->subscriptionRepository->findAll();
    }

    public function findById(int $id): ?Subscription
    {
        return $this->subscriptionRepository->findById($id);
    }

    public function create(CreateSubscriptionDTO $dto): Subscription
    {
        return $this->subscriptionRepository->create($dto);
    }

    public function update(int $id, UpdateSubscriptionDTO $dto): Subscription
    {
        $subscription = $this->subscriptionRepository->findById($id);
        if (! $subscription) {
            throw new \InvalidArgumentException("Subscription with ID {$id} not found.");
        }

        $this->subscriptionRepository->update($subscription, $dto);
        $subscription->refresh();
        $subscription->load(['user', 'plan', 'payments']);

        return $subscription;
    }

    public function delete(int $id): void
    {
        $subscription = $this->subscriptionRepository->findById($id);
        if (! $subscription) {
            throw new \InvalidArgumentException("Subscription with ID {$id} not found.");
        }

        $this->subscriptionRepository->delete($subscription);
    }

    public function hasActiveAccess(int $userId, ?DateTimeInterface $at = null): bool
    {
        return $this->subscriptionRepository->findActiveByUserId($userId, $at) !== null;
    }

    public function expireLapsedSubscriptions(?DateTimeInterface $at = null): int
    {
        throw new \BadMethodCallException('expireLapsedSubscriptions is not implemented yet.');
    }

    public function activateSubscription(int $subscriptionId, ?int $actorId = null): Subscription
    {
        throw new \BadMethodCallException('activateSubscription is not implemented yet.');
    }

    public function cancelSubscription(int $subscriptionId, ?int $actorId = null): Subscription
    {
        throw new \BadMethodCallException('cancelSubscription is not implemented yet.');
    }

    public function renewSubscription(int $subscriptionId, ?DateTimeInterface $newEndsAt = null, ?int $actorId = null): Subscription
    {
        throw new \BadMethodCallException('renewSubscription is not implemented yet.');
    }

    public function grantFreeAccess(int $userId, int $planId, ?int $actorId = null): Subscription
    {
        throw new \BadMethodCallException('grantFreeAccess is not implemented yet.');
    }

    public function syncChannelAccessForUser(int $userId, ?DateTimeInterface $at = null): bool
    {
        throw new \BadMethodCallException('syncChannelAccessForUser is not implemented yet.');
    }
}