<?php

declare(strict_types=1);

namespace App\Modules\Auth\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Modules\Auth\DTO\MfaVerifyDTO;
use App\Modules\Auth\Services\MfaService;

class MfaVerifyProcessor extends Processor
{
    public function __construct(
        private readonly MfaService $mfaService
    ) {}

    /**
     * @return array{verified: bool}
     */
    public function execute(BaseRequest $request): array
    {
        $validated = $request->validated();

        $dto = new MfaVerifyDTO(
            email: $validated['email'],
            password: $validated['password'],
            mfaToken: $validated['mfa_token']
        );

        $verified = $this->mfaService->verify($dto, $request);

        return ['verified' => $verified];
    }
}
