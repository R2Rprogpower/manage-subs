<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Permissions\Contracts\Services\PermissionServiceInterface;
use Illuminate\Http\Request;

class PermissionAssignToRoleProcessor extends Processor
{
    public function __construct(
        private readonly PermissionServiceInterface $permissionService
    ) {}

    public function execute(Request $request, int $roleId, int $permissionId): bool
    {
        $this->permissionService->assignToRole(
            $roleId,
            $permissionId,
            $request->user(),
            $request
        );

        return true;
    }
}
