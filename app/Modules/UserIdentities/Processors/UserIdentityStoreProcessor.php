<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Models\UserIdentity;
use App\Modules\UserIdentities\DTO\CreateUserIdentityDTO;
use App\Modules\UserIdentities\Services\UserIdentityService;

class UserIdentityStoreProcessor extends Processor
{
    public function __construct(
        private readonly UserIdentityService $userIdentityService
    ) {}

    public function execute(BaseRequest $request): UserIdentity
    {
        $validated = $request->validated();

        $dto = new CreateUserIdentityDTO(
            userId: (int) $validated['user_id'],
            provider: $validated['provider'],
            providerUserId: $validated['provider_user_id'],
            username: $validated['username'] ?? null,
            meta: $validated['meta'] ?? null,
        );

        return $this->userIdentityService->create($dto);
    }
}