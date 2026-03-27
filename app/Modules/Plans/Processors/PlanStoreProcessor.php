<?php

declare(strict_types=1);

namespace App\Modules\Plans\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Models\Plan;
use App\Modules\Plans\DTO\CreatePlanDTO;
use App\Modules\Plans\Services\PlanService;

class PlanStoreProcessor extends Processor
{
    public function __construct(
        private readonly PlanService $planService
    ) {}

    public function execute(BaseRequest $request): Plan
    {
        $validated = $request->validated();

        return $this->planService->create(new CreatePlanDTO(
            code: $validated['code'],
            name: $validated['name'],
            priceMinor: (int) $validated['price_minor'],
            currency: $validated['currency'],
            durationDays: isset($validated['duration_days']) ? (int) $validated['duration_days'] : null,
            isActive: (bool) $validated['is_active'],
        ));
    }
}