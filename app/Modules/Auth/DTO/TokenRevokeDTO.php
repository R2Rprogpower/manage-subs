<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTO;

final class TokenRevokeDTO
{
    public function __construct(
        public readonly ?int $tokenId
    ) {}
}
