<?php

declare(strict_types=1);

namespace App\Modules\Plans\Contracts\Services;

use App\Models\Plan;
use App\Modules\Plans\DTO\CreatePlanDTO;
use App\Modules\Plans\DTO\UpdatePlanDTO;
use Illuminate\Database\Eloquent\Collection;

interface PlanServiceInterface
{
    /**
     * @return Collection<int, Plan>
     */
    public function findAll(): Collection;

    public function findById(int $id): ?Plan;

    public function create(CreatePlanDTO $dto): Plan;

    public function update(int $id, UpdatePlanDTO $dto): Plan;

    public function delete(int $id): void;
}
