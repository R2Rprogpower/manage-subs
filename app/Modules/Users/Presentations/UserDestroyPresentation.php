<?php

declare(strict_types=1);

namespace App\Modules\Users\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;

class UserDestroyPresentation extends Presentation implements PresentationInterface
{
    /**
     * @param  bool  $data
     * @return array<string, mixed>
     */
    public function present(mixed $data): array
    {
        return [
            'success' => $data === true,
        ];
    }
}
