<?php

declare(strict_types=1);

namespace App\Modules\Auth\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Models\User;
use App\Modules\Users\DTO\CreateUserDTO;
use App\Modules\Users\Services\UserService;

class SignupProcessor extends Processor
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function execute(BaseRequest $request): User
    {
        $validated = $request->validated();

        $dto = new CreateUserDTO(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password']
        );

        $user = $this->userService->create($dto);

        return $user->fresh(['roles', 'permissions']) ?? $user;
    }
}
