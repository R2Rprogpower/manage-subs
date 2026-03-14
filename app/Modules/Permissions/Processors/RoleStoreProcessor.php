<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Permissions\DTO\CreateRoleDTO;
use App\Modules\Permissions\Http\Requests\StoreRoleRequest;
use App\Modules\Permissions\Services\RoleService;
use Spatie\Permission\Models\Role;

class RoleStoreProcessor extends Processor
{
    public function __construct(
        private readonly RoleService $roleService
    ) {}

    public function execute(StoreRoleRequest $request): Role
    {
        $dto = new CreateRoleDTO(
            name: $request->validated()['name'],
            guardName: $request->validated()['guard_name'] ?? 'web'
        );

        $role = $this->roleService->create($dto, $request->user(), $request);
        $role->load('permissions');

        return $role;
    }
}
