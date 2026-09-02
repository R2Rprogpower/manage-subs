<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Permissions\Contracts\Services\RoleServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class RoleIndexProcessor extends Processor
{
    public function __construct(
        private readonly RoleServiceInterface $roleService
    ) {}

    /**
     * @return Collection<int, Role>
     */
    public function execute(): Collection
    {
        return $this->roleService->findAll();
    }
}
