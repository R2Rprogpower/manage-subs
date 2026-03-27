<?php

declare(strict_types=1);

namespace App\Modules\Auth\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Modules\Auth\Contracts\Services\AuthServiceInterface;
use App\Modules\Auth\DTO\TokenRevokeDTO;

class TokenRevokeProcessor extends Processor
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    /**
     * @return array{revoked: bool}
     */
    public function execute(BaseRequest $request): array
    {
        $validated = $request->validated();

        $dto = new TokenRevokeDTO(
            tokenId: $validated['token_id'] ?? null
        );

        $this->authService->revokeToken($dto, $request);

        return ['revoked' => true];
    }
}
