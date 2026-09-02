<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Processors;

use App\Core\Abstracts\Processor;
use App\Models\UserIdentity;
use App\Modules\UserIdentities\Contracts\Services\UserIdentityServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class UserIdentityIndexProcessor extends Processor
{
    public function __construct(
        private readonly UserIdentityServiceInterface $userIdentityService
    ) {}

    /**
     * @return Collection<int, UserIdentity>
     */
    public function execute(): Collection
    {
        return $this->userIdentityService->findAll();
    }
}
