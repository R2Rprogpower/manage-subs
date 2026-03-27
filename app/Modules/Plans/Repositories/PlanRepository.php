<?php

declare(strict_types=1);

namespace App\Modules\Plans\Repositories;

use App\Models\Plan;
use App\Modules\Plans\DTO\CreatePlanDTO;
use App\Modules\Plans\DTO\UpdatePlanDTO;
use Illuminate\Database\Eloquent\Collection;

class PlanRepository
{
    public function findById(int $id): ?Plan
    {
        /** @var Plan|null */
        return Plan::query()->find($id);
    }

    /**
     * @return Collection<int, Plan>
     */
    public function findAll(): Collection
    {
        return Plan::query()->get();
    }

    public function findByCode(string $code): ?Plan
    {
        /** @var Plan|null */
        return Plan::query()->where('code', $code)->first();
    }

    public function create(CreatePlanDTO $dto): Plan
    {
        /** @var Plan $plan */
        $plan = Plan::query()->create($dto->toArray());

        return $plan;
    }

    public function update(Plan $plan, UpdatePlanDTO $dto): bool
    {
        return $plan->update($dto->toArray());
    }

    public function delete(Plan $plan): bool
    {
        return $plan->delete();
    }
}