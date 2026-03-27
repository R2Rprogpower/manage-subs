<?php

declare(strict_types=1);

namespace App\Modules\Plans\Services;

use App\Models\Plan;
use App\Modules\Plans\DTO\CreatePlanDTO;
use App\Modules\Plans\DTO\UpdatePlanDTO;
use App\Modules\Plans\Repositories\PlanRepository;
use Illuminate\Database\Eloquent\Collection;

class PlanService
{
    public function __construct(
        private readonly PlanRepository $planRepository
    ) {}

    /**
     * @return Collection<int, Plan>
     */
    public function findAll(): Collection
    {
        return $this->planRepository->findAll();
    }

    public function findById(int $id): ?Plan
    {
        return $this->planRepository->findById($id);
    }

    public function create(CreatePlanDTO $dto): Plan
    {
        if ($this->planRepository->findByCode($dto->code)) {
            throw new \InvalidArgumentException("Plan with code '{$dto->code}' already exists.");
        }

        return $this->planRepository->create($dto);
    }

    public function update(int $id, UpdatePlanDTO $dto): Plan
    {
        $plan = $this->planRepository->findById($id);
        if (! $plan) {
            throw new \InvalidArgumentException("Plan with ID {$id} not found.");
        }

        $payload = $dto->toArray();

        if (array_key_exists('code', $payload)) {
            $existing = $this->planRepository->findByCode($payload['code']);
            if ($existing && $existing->id !== $plan->id) {
                throw new \InvalidArgumentException("Plan with code '{$payload['code']}' already exists.");
            }
        }

        $this->planRepository->update($plan, $dto);
        $plan->refresh();

        return $plan;
    }

    public function delete(int $id): void
    {
        $plan = $this->planRepository->findById($id);
        if (! $plan) {
            throw new \InvalidArgumentException("Plan with ID {$id} not found.");
        }

        $this->planRepository->delete($plan);
    }
}