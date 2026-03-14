<?php

declare(strict_types=1);

namespace App\Modules\Users\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Models\User;
use App\Modules\Users\DTO\UpdateUserDTO;
use App\Modules\Users\Services\UserService;

class UserUpdateProcessor extends Processor
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function execute(BaseRequest $request, int $id): User
    {
        $validated = $request->validated();

        $dto = new UpdateUserDTO(
            name: $validated['name'] ?? null,
            email: $validated['email'] ?? null,
            password: $validated['password'] ?? null
        );

        return $this->userService->update(
            $id,
            $dto,
            $request->user(),
            $request
        );
    }
}
