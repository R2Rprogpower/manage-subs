<?php

declare(strict_types=1);

namespace App\Modules\Payments\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Modules\Payments\Services\PaymentService;

class PaymentDestroyProcessor extends Processor
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function execute(BaseRequest $request, int $id): bool
    {
        $this->paymentService->delete($id);

        return true;
    }
}