<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Modules\UserIdentities\Contracts\Services\UserIdentityServiceInterface;

class UserIdentityDestroyProcessor extends Processor
{
    public function __construct(
        private readonly UserIdentityServiceInterface $userIdentityService
    ) {}

    public function execute(BaseRequest $request, int $id): bool
    {
        $this->userIdentityService->delete($id);

        return true;
    }
}