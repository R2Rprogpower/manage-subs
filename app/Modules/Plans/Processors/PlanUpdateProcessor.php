<?php

declare(strict_types=1);

namespace App\Modules\Plans\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Models\Plan;
use App\Modules\Plans\Contracts\Services\PlanServiceInterface;
use App\Modules\Plans\DTO\UpdatePlanDTO;

class PlanUpdateProcessor extends Processor
{
    public function __construct(
        private readonly PlanServiceInterface $planService
    ) {}

    public function execute(BaseRequest $request, int $id): Plan
    {
        $validated = $request->validated();

        $data = [];

        if (array_key_exists('code', $validated)) {
            $data['code'] = $validated['code'];
        }

        if (array_key_exists('name', $validated)) {
            $data['name'] = $validated['name'];
        }

        if (array_key_exists('price_minor', $validated)) {
            $data['price_minor'] = (int) $validated['price_minor'];
        }

        if (array_key_exists('currency', $validated)) {
            $data['currency'] = strtoupper($validated['currency']);
        }

        if (array_key_exists('duration_days', $validated)) {
            $data['duration_days'] = $validated['duration_days'] !== null ? (int) $validated['duration_days'] : null;
        }

        if (array_key_exists('is_active', $validated)) {
            $data['is_active'] = (bool) $validated['is_active'];
        }

        return $this->planService->update($id, new UpdatePlanDTO($data));
    }
}
