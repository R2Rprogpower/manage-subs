<?php

declare(strict_types=1);

namespace App\Modules\Plans\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Plans\Contracts\Services\PlanServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class PlanIndexProcessor extends Processor
{
    public function __construct(
        private readonly PlanServiceInterface $planService
    ) {}

    /**
     * @return Collection<int, \App\Models\Plan>
     */
    public function execute(): Collection
    {
        return $this->planService->findAll();
    }
}
