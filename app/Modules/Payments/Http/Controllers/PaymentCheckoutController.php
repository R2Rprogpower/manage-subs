<?php

declare(strict_types=1);

namespace App\Modules\Payments\Http\Controllers;

use App\Core\Responses\SuccessResponse;
use App\Http\Controllers\Controller;
use App\Infrastructure\Services\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Contracts\Services\PaymentServiceInterface;
use App\Modules\Payments\Http\Requests\CreateLiqPayCheckoutRequest;

class PaymentCheckoutController extends Controller
{
    public function liqpay(
        CreateLiqPayCheckoutRequest $request,
        PaymentServiceInterface $paymentService,
        PaymentGatewayInterface $gateway
    ): SuccessResponse {
        $validated = $request->validated();

        $payment = $paymentService->findById((int) $validated['payment_id']);
        if (! $payment) {
            throw new \InvalidArgumentException('Payment not found.');
        }

        if (strtolower($payment->provider) !== 'liqpay') {
            throw new \InvalidArgumentException('The selected payment is not configured for LiqPay.');
        }

        $checkout = $gateway->createCheckout([
            'action' => $validated['action'] ?? 'pay',
            'amount_minor' => $payment->amount_minor,
            'currency' => $payment->currency,
            'description' => $validated['description'] ?? ('Payment #'.$payment->id),
            'order_id' => 'payment-'.$payment->id,
            'result_url' => $validated['result_url'] ?? null,
            'server_url' => $validated['server_url'] ?? null,
            'sandbox' => $validated['sandbox'] ?? false,
        ]);

        return new SuccessResponse(
            $checkout,
            ['message' => 'LiqPay checkout was created successfully.']
        );
    }
}
