<?php

declare(strict_types=1);

namespace App\Modules\Auth\Processors;

use App\Core\Abstracts\Processor;
use App\Modules\Auth\Contracts\Services\AuthServiceInterface;
use Illuminate\Http\Request;

class LogoutProcessor extends Processor
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    /**
     * @return array{logged_out: bool}
     */
    public function execute(Request $request): array
    {
        $this->authService->logout($request);

        return ['logged_out' => true];
    }
}
