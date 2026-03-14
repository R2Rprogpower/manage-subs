<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Exceptions\BaseException;
use App\Modules\Permissions\Services\PermissionService;
use Spatie\Permission\Models\Permission;

class PermissionShowProcessor extends Processor
{
    public function __construct(
        private readonly PermissionService $permissionService
    ) {}

    public function execute(int $id): Permission
    {
        $permission = $this->permissionService->findById($id);

        if (! $permission) {
            throw new class("Permission with ID {$id} not found.") extends BaseException
            {
                public function getStatusCode(): int
                {
                    return 404;
                }
            };
        }

        return $permission;
    }
}
