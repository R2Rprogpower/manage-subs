<?php

declare(strict_types=1);

namespace App\Modules\Users\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserIndexPresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! $data instanceof Collection) {
            return parent::present($data);
        }

        /** @var Collection<int, User> $data */
        $users = $data->values()->map(function (User $user) {
            return $this->formatUser($user);
        })->toArray();

        return $users;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'created_at' => $user->created_at->toIso8601String(),
            'updated_at' => $user->updated_at->toIso8601String(),
            'roles' => $user->relationLoaded('roles') ? $user->getRoleNames()->toArray() : [],
            'permissions' => $user->relationLoaded('permissions') ? $user->getAllPermissions()->pluck('name')->toArray() : [],
        ];
    }
}
