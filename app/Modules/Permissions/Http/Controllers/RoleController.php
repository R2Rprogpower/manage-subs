<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Http\Controllers;

use App\Core\Responses\SuccessResponse;
use App\Http\Controllers\Controller;
use App\Modules\Permissions\Http\Requests\AssignPermissionRequest;
use App\Modules\Permissions\Http\Requests\AssignRoleRequest;
use App\Modules\Permissions\Http\Requests\StoreRoleRequest;
use App\Modules\Permissions\Http\Requests\UpdateRoleRequest;
use App\Modules\Permissions\Presentations\RoleAssignPermissionsPresentation;
use App\Modules\Permissions\Presentations\RoleAssignToUserPresentation;
use App\Modules\Permissions\Presentations\RoleDestroyPresentation;
use App\Modules\Permissions\Presentations\RoleIndexPresentation;
use App\Modules\Permissions\Presentations\RoleShowPresentation;
use App\Modules\Permissions\Presentations\RoleStorePresentation;
use App\Modules\Permissions\Presentations\RoleUpdatePresentation;
use App\Modules\Permissions\Processors\RoleAssignPermissionsProcessor;
use App\Modules\Permissions\Processors\RoleAssignToUserProcessor;
use App\Modules\Permissions\Processors\RoleDestroyProcessor;
use App\Modules\Permissions\Processors\RoleIndexProcessor;
use App\Modules\Permissions\Processors\RoleShowProcessor;
use App\Modules\Permissions\Processors\RoleStoreProcessor;
use App\Modules\Permissions\Processors\RoleUpdateProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    public function index(
        RoleIndexProcessor $processor,
        RoleIndexPresentation $presentation
    ): SuccessResponse {
        $roles = $processor->execute();

        return new SuccessResponse(
            $presentation->present($roles)
        );
    }

    public function show(
        RoleShowProcessor $processor,
        RoleShowPresentation $presentation,
        int $id
    ): SuccessResponse {
        $role = $processor->execute($id);

        return new SuccessResponse(
            $presentation->present($role)
        );
    }

    public function store(
        StoreRoleRequest $request,
        RoleStoreProcessor $processor,
        RoleStorePresentation $presentation
    ): SuccessResponse {
        $role = $processor->execute($request);

        return new SuccessResponse(
            $presentation->present($role),
            ['message' => 'Role was created successfully'],
            Response::HTTP_CREATED
        );
    }

    public function update(
        UpdateRoleRequest $request,
        RoleUpdateProcessor $processor,
        RoleUpdatePresentation $presentation,
        int $id
    ): SuccessResponse {
        $role = $processor->execute($request, $id);

        return new SuccessResponse(
            $presentation->present($role),
            ['message' => 'Role was updated successfully']
        );
    }

    public function destroy(
        Request $request,
        RoleDestroyProcessor $processor,
        RoleDestroyPresentation $presentation,
        int $id
    ): SuccessResponse {
        $result = $processor->execute($request, $id);

        return new SuccessResponse(
            $presentation->present($result),
            ['message' => 'Role was deleted successfully']
        );
    }

    public function assignPermissions(
        AssignPermissionRequest $request,
        RoleAssignPermissionsProcessor $processor,
        RoleAssignPermissionsPresentation $presentation,
        int $id
    ): SuccessResponse {
        $role = $processor->execute($request, $id);

        return new SuccessResponse(
            $presentation->present($role),
            ['message' => 'Permissions assigned to role successfully']
        );
    }

    public function assignRole(
        AssignRoleRequest $request,
        RoleAssignToUserProcessor $processor,
        RoleAssignToUserPresentation $presentation,
        int $userId
    ): SuccessResponse {
        $result = $processor->execute($request, $userId);

        return new SuccessResponse(
            $presentation->present($result),
            ['message' => 'Role assigned to user successfully']
        );
    }
}
