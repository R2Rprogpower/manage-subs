<?php

declare(strict_types=1);

namespace App\Modules\Users\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Exceptions\BaseException;
use App\Models\User;
use App\Modules\Users\Services\UserService;

class UserShowProcessor extends Processor
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function execute(int $id): User
    {
        $user = $this->userService->findById($id);

        if (! $user) {
            throw new class("User with ID {$id} not found.") extends BaseException
            {
                public function getStatusCode(): int
                {
                    return 404;
                }
            };
        }

        return $user;
    }
}
