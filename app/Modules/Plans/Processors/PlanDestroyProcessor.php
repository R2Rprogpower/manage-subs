<?php

declare(strict_types=1);

namespace App\Modules\Plans\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Modules\Plans\Contracts\Services\PlanServiceInterface;

class PlanDestroyProcessor extends Processor
{
    public function __construct(
        private readonly PlanServiceInterface $planService
    ) {}

    public function execute(BaseRequest $request, int $id): bool
    {
        $this->planService->delete($id);

        return true;
    }
}
