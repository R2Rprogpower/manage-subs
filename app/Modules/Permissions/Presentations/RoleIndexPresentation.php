<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use Illuminate\Database\Eloquent\Collection;

class RoleIndexPresentation extends Presentation implements PresentationInterface
{
    /**
     * @param  Collection<int, \Spatie\Permission\Models\Role>  $data
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! $data instanceof Collection) {
            return parent::present($data);
        }

        $roles = $data->map(function ($role) {
            return $this->formatRole($role);
        })->toArray();

        return $roles;
    }

    /**
     * @param  \Spatie\Permission\Models\Role  $role
     * @return array<string, mixed>
     */
    private function formatRole($role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'created_at' => $role->created_at?->toIso8601String(),
            'updated_at' => $role->updated_at?->toIso8601String(),
            'permissions' => $role->relationLoaded('permissions')
                ? $role->getRelation('permissions')->pluck('name')->toArray()
                : [],
        ];
    }
}
