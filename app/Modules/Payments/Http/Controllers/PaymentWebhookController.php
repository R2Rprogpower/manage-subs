<?php

declare(strict_types=1);

namespace App\Modules\Payments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Infrastructure\Services\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Contracts\Repositories\PaymentRepositoryInterface;
use App\Modules\Payments\Contracts\Services\PaymentServiceInterface;
use App\Modules\Payments\DTO\UpdatePaymentDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function liqpay(
        Request $request,
        PaymentGatewayInterface $gateway,
        PaymentRepositoryInterface $paymentRepository,
        PaymentServiceInterface $paymentService
    ): JsonResponse {
        $rawPayload = $request->getContent();
        if ($rawPayload === '' || ! str_contains($rawPayload, 'data=')) {
            $rawPayload = http_build_query([
                'data' => (string) $request->input('data', ''),
                'signature' => (string) $request->input('signature', ''),
            ]);
        }

        if (! $gateway->verifyWebhook($rawPayload, $request->headers->all())) {
            return response()->json(['message' => 'Invalid webhook signature.'], 400);
        }

        $event = $gateway->normalizeWebhookEvent($rawPayload, $request->headers->all());
        $providerPaymentId = trim((string) ($event['provider_payment_id'] ?? ''));

        $payment = null;
        if ($providerPaymentId !== '') {
            $payment = $paymentRepository->findByProviderPaymentId('liqpay', $providerPaymentId);
        }

        if (! $payment && isset($event['order_id']) && preg_match('/^payment-(\d+)$/', (string) $event['order_id'], $matches) === 1) {
            $payment = $paymentRepository->findById((int) $matches[1]);
        }

        if (! $payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        if ($providerPaymentId !== '' && $payment->provider_payment_id !== $providerPaymentId) {
            $paymentService->update($payment->id, new UpdatePaymentDTO([
                'provider_payment_id' => $providerPaymentId,
            ]));
        }

        $status = (string) ($event['internal_status'] ?? 'pending');
        if ($status === 'paid') {
            $paymentService->markPaid($payment->id, now());
            $paymentService->onPaymentStateChanged($payment->id);
        } elseif ($status === 'failed' || $status === 'refunded') {
            $paymentService->markFailed($payment->id);
            $paymentService->onPaymentStateChanged($payment->id);
        }

        return response()->json(['ok' => true]);
    }
}
