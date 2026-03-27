<?php

declare(strict_types=1);

namespace App\Modules\Plans\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Exceptions\BaseException;
use App\Models\Plan;
use App\Modules\Plans\Contracts\Services\PlanServiceInterface;

class PlanShowProcessor extends Processor
{
    public function __construct(
        private readonly PlanServiceInterface $planService
    ) {}

    public function execute(int $id): Plan
    {
        $plan = $this->planService->findById($id);

        if (! $plan) {
            throw new class("Plan with ID {$id} not found.") extends BaseException
            {
                public function getStatusCode(): int
                {
                    return 404;
                }
            };
        }

        return $plan;
    }
}