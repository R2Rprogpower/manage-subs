<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use Spatie\Permission\Models\Permission;

class PermissionShowPresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! $data instanceof Permission) {
            return parent::present($data);
        }

        return [
            'id' => $data->id,
            'name' => $data->name,
            'guard_name' => $data->guard_name,
            'created_at' => $data->created_at?->toIso8601String(),
            'updated_at' => $data->updated_at?->toIso8601String(),
        ];
    }
}
