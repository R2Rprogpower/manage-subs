<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use App\Models\User;

class SignupPresentation extends Presentation implements PresentationInterface
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
            'user' => [
                'id' => $data->id,
                'name' => $data->name,
                'email' => $data->email,
                'roles' => $data->getRoleNames()->toArray(),
                'permissions' => $data->getAllPermissions()->pluck('name')->toArray(),
            ],
            'mfa_required' => true,
        ];
    }
}
