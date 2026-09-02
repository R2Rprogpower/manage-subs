<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Permissions\Contracts\Services\PermissionServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

class PermissionIndexProcessor extends Processor
{
    public function __construct(
        private readonly PermissionServiceInterface $permissionService
    ) {}

    /**
     * @return Collection<int, Permission>
     */
    public function execute(): Collection
    {
        return $this->permissionService->findAll();
    }
}
