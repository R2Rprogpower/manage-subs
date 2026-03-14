<?php

declare(strict_types=1);

namespace App\Modules\Users\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'created_at' => $user->created_at->toIso8601String(),
            'updated_at' => $user->updated_at->toIso8601String(),
            'roles' => $user->relationLoaded('roles') ? $user->getRoleNames()->toArray() : null,
            'permissions' => $user->relationLoaded('permissions') ? $user->getAllPermissions()->pluck('name')->toArray() : null,
        ];
    }
}
