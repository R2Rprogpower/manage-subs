<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Exceptions\BaseException;
use App\Models\UserIdentity;
use App\Modules\UserIdentities\Contracts\Services\UserIdentityServiceInterface;

class UserIdentityShowProcessor extends Processor
{
    public function __construct(
        private readonly UserIdentityServiceInterface $userIdentityService
    ) {}

    public function execute(int $id): UserIdentity
    {
        $userIdentity = $this->userIdentityService->findById($id);

        if (! $userIdentity) {
            throw new class("User identity with ID {$id} not found.") extends BaseException
            {
                public function getStatusCode(): int
                {
                    return 404;
                }
            };
        }

        return $userIdentity;
    }
}
