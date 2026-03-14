<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;

class MfaSetupPresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! is_array($data)) {
            return parent::present($data);
        }

        return [
            'secret' => $data['secret'] ?? null,
            'otpauth_url' => $data['otpauth_url'] ?? null,
            'recovery_codes' => $data['recovery_codes'] ?? [],
        ];
    }
}
