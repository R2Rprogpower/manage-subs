<?php

declare(strict_types=1);

namespace App\Modules\Payments\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Exceptions\BaseException;
use App\Models\Payment;
use App\Modules\Payments\Services\PaymentService;

class PaymentShowProcessor extends Processor
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function execute(int $id): Payment
    {
        $payment = $this->paymentService->findById($id);

        if (! $payment) {
            throw new class("Payment with ID {$id} not found.") extends BaseException
            {
                public function getStatusCode(): int
                {
                    return 404;
                }
            };
        }

        return $payment;
    }
}