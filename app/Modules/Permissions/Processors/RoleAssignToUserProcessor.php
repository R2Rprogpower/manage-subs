<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Permissions\Http\Requests\AssignRoleRequest;
use App\Modules\Permissions\Contracts\Services\RoleServiceInterface;

class RoleAssignToUserProcessor extends Processor
{
    public function __construct(
        private readonly RoleServiceInterface $roleService
    ) {}

    public function execute(AssignRoleRequest $request, int $userId): bool
    {
        $this->roleService->assignRoleToUser(
            $userId,
            $request->validated()['role_name'],
            $request->user(),
            $request
        );

        return true;
    }
}
