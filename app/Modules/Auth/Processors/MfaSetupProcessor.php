<?php

declare(strict_types=1);

namespace App\Modules\Auth\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Modules\Auth\Contracts\Services\MfaServiceInterface;
use App\Modules\Auth\DTO\MfaSetupDTO;

class MfaSetupProcessor extends Processor
{
    public function __construct(
        private readonly MfaServiceInterface $mfaService
    ) {}

    /**
     * @return array{secret: string, otpauth_url: string, recovery_codes: array<int, string>}
     */
    public function execute(BaseRequest $request): array
    {
        $validated = $request->validated();

        $dto = new MfaSetupDTO(
            email: $validated['email'],
            password: $validated['password']
        );

        return $this->mfaService->setup($dto, $request);
    }
}
