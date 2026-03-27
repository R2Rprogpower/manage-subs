<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Contracts\Services;

use App\Models\Subscription;
use App\Modules\Subscriptions\DTO\CreateSubscriptionDTO;
use App\Modules\Subscriptions\DTO\UpdateSubscriptionDTO;
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
}