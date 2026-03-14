<?php

declare(strict_types=1);

namespace App\Modules\Auth\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Modules\Auth\DTO\LoginDTO;
use App\Modules\Auth\Services\AuthService;

class LoginProcessor extends Processor
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * @return array{user: \App\Models\User, token: string}
     */
    public function execute(BaseRequest $request): array
    {
        $validated = $request->validated();

        $dto = new LoginDTO(
            email: $validated['email'],
            password: $validated['password'],
            mfaToken: $validated['mfa_token'] ?? $request->header('X-MFA-Token')
        );

        return $this->authService->login($dto, $request);
    }
}
