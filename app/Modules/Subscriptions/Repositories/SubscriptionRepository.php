<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Repositories;

use App\Models\Subscription;
use App\Modules\Subscriptions\Contracts\Repositories\SubscriptionRepositoryInterface;
use App\Modules\Subscriptions\DTO\CreateSubscriptionDTO;
use App\Modules\Subscriptions\DTO\UpdateSubscriptionDTO;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function findById(int $id): ?Subscription
    {
        /** @var Subscription|null */
        return Subscription::query()
            ->with(['user', 'plan', 'payments'])
            ->find($id);
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function findAll(): Collection
    {
        return Subscription::query()
            ->with(['user', 'plan', 'payments'])
            ->get();
    }

    public function create(CreateSubscriptionDTO $dto): Subscription
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::query()->create($dto->toArray());
        $subscription->load(['user', 'plan', 'payments']);

        return $subscription;
    }

    public function update(Subscription $subscription, UpdateSubscriptionDTO $dto): bool
    {
        return $subscription->update($dto->toArray());
    }

    public function delete(Subscription $subscription): bool
    {
        return $subscription->delete();
    }
}