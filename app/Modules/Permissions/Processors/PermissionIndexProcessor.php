<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Permissions\Services\PermissionService;
use Illuminate\Database\Eloquent\Collection;

class PermissionIndexProcessor extends Processor
{
    public function __construct(
        private readonly PermissionService $permissionService
    ) {}

    /**
     * @return Collection<int, \Spatie\Permission\Models\Permission>
     */
    public function execute(): Collection
    {
        return $this->permissionService->findAll();
    }
}
