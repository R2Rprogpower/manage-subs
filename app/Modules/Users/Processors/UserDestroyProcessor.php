<?php

declare(strict_types=1);

namespace App\Modules\Users\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Modules\Users\Contracts\Services\UserServiceInterface;

class UserDestroyProcessor extends Processor
{
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    public function execute(BaseRequest $request, int $id): bool
    {
        $this->userService->delete(
            $id,
            $request->user(),
            $request
        );

        return true;
    }
}
