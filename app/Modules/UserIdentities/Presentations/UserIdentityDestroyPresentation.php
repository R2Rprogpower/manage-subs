<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;

class UserIdentityDestroyPresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<string, mixed>
     */
    public function present(mixed $data): array
    {
        return [
            'success' => $data === true,
        ];
    }
}
