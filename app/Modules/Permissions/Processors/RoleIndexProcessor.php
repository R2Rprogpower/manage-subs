<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Permissions\Services\RoleService;
use Illuminate\Database\Eloquent\Collection;

class RoleIndexProcessor extends Processor
{
    public function __construct(
        private readonly RoleService $roleService
    ) {}

    /**
     * @return Collection<int, \Spatie\Permission\Models\Role>
     */
    public function execute(): Collection
    {
        return $this->roleService->findAll();
    }
}
