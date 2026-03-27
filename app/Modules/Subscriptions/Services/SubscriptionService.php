<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Services;

use App\Models\Subscription;
use App\Modules\Subscriptions\DTO\CreateSubscriptionDTO;
use App\Modules\Subscriptions\DTO\UpdateSubscriptionDTO;
use App\Modules\Subscriptions\Repositories\SubscriptionRepository;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository
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
}