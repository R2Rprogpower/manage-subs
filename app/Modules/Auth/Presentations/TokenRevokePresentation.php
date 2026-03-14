<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;

class TokenRevokePresentation extends Presentation implements PresentationInterface
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
            'revoked' => (bool) ($data['revoked'] ?? false),
        ];
    }
}
