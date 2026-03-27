<?php

declare(strict_types=1);

namespace App\Modules\Plans\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use App\Models\Plan;

class PlanPresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! $data instanceof Plan) {
            return parent::present($data);
        }

        return [
            'id' => $data->id,
            'code' => $data->code,
            'name' => $data->name,
            'price_minor' => $data->price_minor,
            'currency' => $data->currency,
            'duration_days' => $data->duration_days,
            'is_active' => $data->is_active,
            'created_at' => $data->created_at?->toIso8601String(),
            'updated_at' => $data->updated_at?->toIso8601String(),
        ];
    }
}