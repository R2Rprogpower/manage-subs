<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentations;

use App\Core\Abstracts\Presentation;
use App\Core\Interfaces\PresentationInterface;

class PaymentDestroyPresentation extends Presentation implements PresentationInterface
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