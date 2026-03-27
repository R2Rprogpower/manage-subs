<?php

declare(strict_types=1);

namespace App\Modules\Plans\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

class PlanIndexPresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! $data instanceof Collection) {
            return parent::present($data);
        }

        /** @var Collection<int, Plan> $data */
        return $data->values()->map(fn (Plan $plan): array => [
            'id' => $plan->id,
            'code' => $plan->code,
            'name' => $plan->name,
            'price_minor' => $plan->price_minor,
            'currency' => $plan->currency,
            'duration_days' => $plan->duration_days,
            'is_active' => $plan->is_active,
            'created_at' => $plan->created_at?->toIso8601String(),
            'updated_at' => $plan->updated_at?->toIso8601String(),
        ])->toArray();
    }
}
