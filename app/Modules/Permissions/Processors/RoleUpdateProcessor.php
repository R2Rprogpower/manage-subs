<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Permissions\DTO\UpdateRoleDTO;
use App\Modules\Permissions\Http\Requests\UpdateRoleRequest;
use App\Modules\Permissions\Services\RoleService;
use Spatie\Permission\Models\Role;

class RoleUpdateProcessor extends Processor
{
    public function __construct(
        private readonly RoleService $roleService
    ) {}

    public function execute(UpdateRoleRequest $request, int $id): Role
    {
        $dto = new UpdateRoleDTO(
            name: $request->validated()['name'] ?? null,
            guardName: $request->validated()['guard_name'] ?? null
        );

        $role = $this->roleService->update($id, $dto, $request->user(), $request);
        $role->load('permissions');

        return $role;
    }
}
