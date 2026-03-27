<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Contracts\Repositories;

use App\Models\Subscription;
use App\Modules\Subscriptions\DTO\CreateSubscriptionDTO;
use App\Modules\Subscriptions\DTO\UpdateSubscriptionDTO;
use Illuminate\Database\Eloquent\Collection;

interface SubscriptionRepositoryInterface
{
    public function findById(int $id): ?Subscription;

    /**
     * @return Collection<int, Subscription>
     */
    public function findAll(): Collection;

    public function create(CreateSubscriptionDTO $dto): Subscription;

    public function update(Subscription $subscription, UpdateSubscriptionDTO $dto): bool;

    public function delete(Subscription $subscription): bool;
}