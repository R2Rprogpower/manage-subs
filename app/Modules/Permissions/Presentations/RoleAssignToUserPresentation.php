<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;

class RoleAssignToUserPresentation extends Presentation implements PresentationInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function present(mixed $data): array
    {
        if (! is_bool($data)) {
            return parent::present($data);
        }

        return [];
    }
}
