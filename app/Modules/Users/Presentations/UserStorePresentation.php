<?php

declare(strict_types=1);

namespace App\Modules\Users\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use App\Models\User;

class UserStorePresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! $data instanceof User) {
            return parent::present($data);
        }

        return [
            'id' => $data->id,
            'name' => $data->name,
            'email' => $data->email,
            'email_verified_at' => $data->email_verified_at?->toIso8601String(),
            'created_at' => $data->created_at->toIso8601String(),
            'updated_at' => $data->updated_at->toIso8601String(),
            'roles' => $data->relationLoaded('roles') ? $data->getRoleNames()->toArray() : [],
            'permissions' => $data->relationLoaded('permissions') ? $data->getAllPermissions()->pluck('name')->toArray() : [],
        ];
    }
}
