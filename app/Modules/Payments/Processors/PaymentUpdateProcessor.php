<?php

declare(strict_types=1);

namespace App\Modules\Payments\Processors;

use App\Core\Abstracts\Processor;
use App\Core\Abstracts\Request as BaseRequest;
use App\Models\Payment;
use App\Modules\Payments\DTO\UpdatePaymentDTO;
use App\Modules\Payments\Contracts\Services\PaymentServiceInterface;

class PaymentUpdateProcessor extends Processor
{
    public function __construct(
        private readonly PaymentServiceInterface $paymentService
    ) {}

    public function execute(BaseRequest $request, int $id): Payment
    {
        $validated = $request->validated();

        $data = [];

        if (array_key_exists('subscription_id', $validated)) {
            $data['subscription_id'] = (int) $validated['subscription_id'];
        }

        if (array_key_exists('provider', $validated)) {
            $data['provider'] = $validated['provider'];
        }

        if (array_key_exists('provider_payment_id', $validated)) {
            $data['provider_payment_id'] = $validated['provider_payment_id'];
        }

        if (array_key_exists('status', $validated)) {
            $data['status'] = $validated['status'];
        }

        if (array_key_exists('amount_minor', $validated)) {
            $data['amount_minor'] = (int) $validated['amount_minor'];
        }

        if (array_key_exists('currency', $validated)) {
            $data['currency'] = strtoupper($validated['currency']);
        }

        if (array_key_exists('paid_at', $validated)) {
            $data['paid_at'] = $validated['paid_at'];
        }

        return $this->paymentService->update($id, new UpdatePaymentDTO($data));
    }
}