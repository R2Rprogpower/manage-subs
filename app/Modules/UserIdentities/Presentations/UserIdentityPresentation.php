<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;
use App\Models\UserIdentity;

class UserIdentityPresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! $data instanceof UserIdentity) {
            return parent::present($data);
        }

        return $this->formatUserIdentity($data);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatUserIdentity(UserIdentity $userIdentity): array
    {
        return [
            'id' => $userIdentity->id,
            'user_id' => $userIdentity->user_id,
            'provider' => $userIdentity->provider,
            'provider_user_id' => $userIdentity->provider_user_id,
            'username' => $userIdentity->username,
            'meta' => $userIdentity->meta,
            'created_at' => $userIdentity->created_at?->toIso8601String(),
            'updated_at' => $userIdentity->updated_at?->toIso8601String(),
            'user' => $userIdentity->relationLoaded('user') && $userIdentity->user !== null ? [
                'id' => $userIdentity->user->id,
                'email' => $userIdentity->user->email,
                'name' => $userIdentity->user->name,
            ] : null,
        ];
    }
}
