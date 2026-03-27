<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Contracts\Services;

use App\Models\Subscription;
use App\Modules\Subscriptions\DTO\CreateSubscriptionDTO;
use App\Modules\Subscriptions\DTO\UpdateSubscriptionDTO;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;

interface SubscriptionServiceInterface
{
    /**
     * @return Collection<int, Subscription>
     */
    public function findAll(): Collection;

    public function findById(int $id): ?Subscription;

    public function create(CreateSubscriptionDTO $dto): Subscription;

    public function update(int $id, UpdateSubscriptionDTO $dto): Subscription;

    public function delete(int $id): void;

    public function hasActiveAccess(int $userId, ?DateTimeInterface $at = null): bool;

    public function expireLapsedSubscriptions(?DateTimeInterface $at = null): int;

    public function activateSubscription(int $subscriptionId, ?int $actorId = null): Subscription;

    public function cancelSubscription(int $subscriptionId, ?int $actorId = null): Subscription;

    public function renewSubscription(int $subscriptionId, ?DateTimeInterface $newEndsAt = null, ?int $actorId = null): Subscription;

    public function grantFreeAccess(int $userId, int $planId, ?int $actorId = null): Subscription;

    public function syncChannelAccessForUser(int $userId, ?DateTimeInterface $at = null): bool;
}
