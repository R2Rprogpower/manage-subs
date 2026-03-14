<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTO;

final class LoginDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $mfaToken
    ) {}
}
