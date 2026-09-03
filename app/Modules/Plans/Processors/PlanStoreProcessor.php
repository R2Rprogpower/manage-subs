<?php

declare(strict_types=1);

namespace App\Modules\Plans\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Models\Plan;
use App\Modules\Plans\Contracts\Services\PlanServiceInterface;
use App\Modules\Plans\DTO\CreatePlanDTO;
use App\Modules\Plans\Enums\PlanKind;

class PlanStoreProcessor extends Processor
{
    public function __construct(
        private readonly PlanServiceInterface $planService
    ) {}

    public function execute(BaseRequest $request): Plan
    {
        $validated = $request->validated();

        return $this->planService->create(new CreatePlanDTO(
            telegramChannelId: (int) $validated['telegram_channel_id'],
            code: $validated['code'],
            name: $validated['name'],
            kind: $validated['kind'],
            priceMinor: (int) ($validated['price_minor'] ?? 0),
            currency: $validated['currency'] ?? 'USD',
            durationDays: isset($validated['duration_days']) ? (int) $validated['duration_days'] : null,
            configuration: $validated['configuration'] ?? ($validated['kind'] === PlanKind::ACHIEVEMENT->value ? [] : null),
            isActive: (bool) $validated['is_active'],
        ));
    }
}
