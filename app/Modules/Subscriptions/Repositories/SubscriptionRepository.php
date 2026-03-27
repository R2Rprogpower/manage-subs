<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Repositories;

use App\Models\Subscription;
use App\Modules\Subscriptions\Contracts\Repositories\SubscriptionRepositoryInterface;
use App\Modules\Subscriptions\DTO\CreateSubscriptionDTO;
use App\Modules\Subscriptions\DTO\UpdateSubscriptionDTO;
use DateTimeInterface;
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

    public function findActiveByUserId(int $userId, ?DateTimeInterface $at = null): ?Subscription
    {
        $moment = $at?->format('Y-m-d H:i:s') ?? now()->toDateTimeString();

        /** @var Subscription|null */
        return Subscription::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where(function ($query) use ($moment): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', $moment);
            })
            ->orderByDesc('started_at')
            ->first();
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function findLapsedActive(?DateTimeInterface $at = null): Collection
    {
        $moment = $at?->format('Y-m-d H:i:s') ?? now()->toDateTimeString();

        return Subscription::query()
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', $moment)
            ->get();
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function findByUserId(int $userId): Collection
    {
        return Subscription::query()
            ->with(['user', 'plan', 'payments'])
            ->where('user_id', $userId)
            ->orderByDesc('started_at')
            ->get();
    }
}