<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Exceptions\BaseException;
use App\Modules\Permissions\Services\RoleService;
use Spatie\Permission\Models\Role;

class RoleShowProcessor extends Processor
{
    public function __construct(
        private readonly RoleService $roleService
    ) {}

    public function execute(int $id): Role
    {
        $role = $this->roleService->findById($id);

        if (! $role) {
            throw new class("Role with ID {$id} not found.") extends BaseException
            {
                public function getStatusCode(): int
                {
                    return 404;
                }
            };
        }

        $role->load('permissions');

        return $role;
    }
}
