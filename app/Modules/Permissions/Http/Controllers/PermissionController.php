<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Http\Controllers;

use App\Core\Responses\SuccessResponse;
use App\Http\Controllers\Controller;
use App\Modules\Permissions\Presentations\PermissionAssignToRolePresentation;
use App\Modules\Permissions\Presentations\PermissionAssignToUserPresentation;
use App\Modules\Permissions\Presentations\PermissionIndexPresentation;
use App\Modules\Permissions\Presentations\PermissionShowPresentation;
use App\Modules\Permissions\Processors\PermissionAssignToRoleProcessor;
use App\Modules\Permissions\Processors\PermissionAssignToUserProcessor;
use App\Modules\Permissions\Processors\PermissionIndexProcessor;
use App\Modules\Permissions\Processors\PermissionShowProcessor;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(
        PermissionIndexProcessor $processor,
        PermissionIndexPresentation $presentation
    ): SuccessResponse {
        $permissions = $processor->execute();

        return new SuccessResponse(
            $presentation->present($permissions)
        );
    }

    public function show(
        PermissionShowProcessor $processor,
        PermissionShowPresentation $presentation,
        int $id
    ): SuccessResponse {
        $permission = $processor->execute($id);

        return new SuccessResponse(
            $presentation->present($permission)
        );
    }

    public function assignToUser(
        Request $request,
        PermissionAssignToUserProcessor $processor,
        PermissionAssignToUserPresentation $presentation,
        int $userId,
        int $permissionId
    ): SuccessResponse {
        $result = $processor->execute($request, $userId, $permissionId);

        return new SuccessResponse(
            $presentation->present($result),
            ['message' => 'Permission assigned to user successfully']
        );
    }

    public function assignToRole(
        Request $request,
        PermissionAssignToRoleProcessor $processor,
        PermissionAssignToRolePresentation $presentation,
        int $roleId,
        int $permissionId
    ): SuccessResponse {
        $result = $processor->execute($request, $roleId, $permissionId);

        return new SuccessResponse(
            $presentation->present($result),
            ['message' => 'Permission assigned to role successfully']
        );
    }
}
