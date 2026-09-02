<?php

declare(strict_types=1);

namespace App\Modules\Users\Processors;

use App\Core\Abstracts\Processor;
use App\Models\User;
use App\Modules\Users\Contracts\Services\UserServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class UserIndexProcessor extends Processor
{
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    /**
     * @return Collection<int, User>
     */
    public function execute(): Collection
    {
        return $this->userService->findAll();
    }
}
