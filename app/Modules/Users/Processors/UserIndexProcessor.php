<?php

declare(strict_types=1);

namespace App\Modules\Users\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Users\Services\UserService;
use Illuminate\Database\Eloquent\Collection;

class UserIndexProcessor extends Processor
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * @return Collection<int, \App\Models\User>
     */
    public function execute(): Collection
    {
        return $this->userService->findAll();
    }
}
