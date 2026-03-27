<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Models\UserIdentity;
use App\Modules\UserIdentities\Contracts\Services\UserIdentityServiceInterface;
use App\Modules\UserIdentities\DTO\UpdateUserIdentityDTO;

class UserIdentityUpdateProcessor extends Processor
{
    public function __construct(
        private readonly UserIdentityServiceInterface $userIdentityService
    ) {}

    public function execute(BaseRequest $request, int $id): UserIdentity
    {
        $validated = $request->validated();

        $data = [];

        if (array_key_exists('user_id', $validated)) {
            $data['user_id'] = (int) $validated['user_id'];
        }

        if (array_key_exists('provider', $validated)) {
            $data['provider'] = $validated['provider'];
        }

        if (array_key_exists('provider_user_id', $validated)) {
            $data['provider_user_id'] = $validated['provider_user_id'];
        }

        if (array_key_exists('username', $validated)) {
            $data['username'] = $validated['username'];
        }

        if (array_key_exists('meta', $validated)) {
            $data['meta'] = $validated['meta'];
        }

        $dto = new UpdateUserIdentityDTO($data);

        return $this->userIdentityService->update($id, $dto);
    }
}
