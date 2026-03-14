<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Permissions\Http\Requests\AssignPermissionRequest;
use App\Modules\Permissions\Services\RoleService;
use Spatie\Permission\Models\Role;

class RoleAssignPermissionsProcessor extends Processor
{
    public function __construct(
        private readonly RoleService $roleService
    ) {}

    public function execute(AssignPermissionRequest $request, int $id): Role
    {
        $role = $this->roleService->assignPermissions(
            $id,
            $request->validated()['permission_ids'],
            $request->user(),
            $request
        );
        $role->load('permissions');

        return $role;
    }
}
