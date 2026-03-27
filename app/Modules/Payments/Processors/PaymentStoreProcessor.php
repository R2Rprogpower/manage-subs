<?php

declare(strict_types=1);

namespace App\Modules\Payments\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Models\Payment;
use App\Modules\Payments\DTO\CreatePaymentDTO;
use App\Modules\Payments\Services\PaymentService;

class PaymentStoreProcessor extends Processor
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function execute(BaseRequest $request): Payment
    {
        $validated = $request->validated();

        return $this->paymentService->create(new CreatePaymentDTO(
            subscriptionId: (int) $validated['subscription_id'],
            provider: $validated['provider'],
            providerPaymentId: $validated['provider_payment_id'] ?? null,
            status: $validated['status'],
            amountMinor: (int) $validated['amount_minor'],
            currency: $validated['currency'],
            paidAt: $validated['paid_at'] ?? null,
        ));
    }
}