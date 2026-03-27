<?php

declare(strict_types=1);

namespace App\Modules\Plans\Contracts\Repositories;

use App\Models\Plan;
use App\Modules\Plans\DTO\CreatePlanDTO;
use App\Modules\Plans\DTO\UpdatePlanDTO;
use Illuminate\Database\Eloquent\Collection;

interface PlanRepositoryInterface
{
    public function findById(int $id): ?Plan;

    /**
     * @return Collection<int, Plan>
     */
    public function findAll(): Collection;

    public function findByCode(string $code): ?Plan;

    public function create(CreatePlanDTO $dto): Plan;

    public function update(Plan $plan, UpdatePlanDTO $dto): bool;

    public function delete(Plan $plan): bool;
}