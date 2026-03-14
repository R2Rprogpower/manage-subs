<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Permissions\Services\RoleService;
use Illuminate\Http\Request;

class RoleDestroyProcessor extends Processor
{
    public function __construct(
        private readonly RoleService $roleService
    ) {}

    public function execute(Request $request, int $id): bool
    {
        return $this->roleService->delete($id, $request->user(), $request);
    }
}
