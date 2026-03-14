<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use Illuminate\Database\Eloquent\Collection;

class PermissionIndexPresentation extends Presentation implements PresentationInterface
{
    /**
     * @param  Collection<int, \Spatie\Permission\Models\Permission>  $data
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! $data instanceof Collection) {
            return parent::present($data);
        }

        $permissions = $data->map(function ($permission) {
            return $this->formatPermission($permission);
        })->toArray();

        return $permissions;
    }

    /**
     * @param  \Spatie\Permission\Models\Permission  $permission
     * @return array<string, mixed>
     */
    private function formatPermission($permission): array
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
            'created_at' => $permission->created_at?->toIso8601String(),
            'updated_at' => $permission->updated_at?->toIso8601String(),
        ];
    }
}
