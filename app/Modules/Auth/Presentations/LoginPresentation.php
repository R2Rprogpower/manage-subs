<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use App\Models\User;

class LoginPresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! is_array($data)) {
            return parent::present($data);
        }

        $user = $data['user'] ?? null;
        if (! $user instanceof User) {
            return parent::present($data);
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->toArray(),
                'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            ],
            'access_token' => $data['token'] ?? null,
            'token_type' => 'Bearer',
        ];
    }
}
