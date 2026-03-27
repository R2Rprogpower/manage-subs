<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Infrastructure\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class LiqPayGateway implements PaymentGatewayInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createCheckout(array $payload): array
    {
        $amountMinor = (int) ($payload['amount_minor'] ?? 0);
        $currency = strtoupper((string) ($payload['currency'] ?? 'UAH'));

        $requestPayload = [
            'public_key' => $this->publicKey(),
            'version' => 3,
            'action' => (string) ($payload['action'] ?? 'pay'),
            'amount' => $amountMinor / 100,
            'currency' => $currency,
            'description' => (string) ($payload['description'] ?? 'Payment'),
            'order_id' => (string) ($payload['order_id'] ?? ''),
        ];

        if (! empty($payload['result_url'])) {
            $requestPayload['result_url'] = (string) $payload['result_url'];
        }

        if (! empty($payload['server_url'])) {
            $requestPayload['server_url'] = (string) $payload['server_url'];
        }

        if (array_key_exists('sandbox', $payload)) {
            $requestPayload['sandbox'] = (int) ((bool) $payload['sandbox']);
        }

        $data = $this->encodePayload($requestPayload);
        $signature = $this->makeSignature($data);

        return [
            'provider' => 'liqpay',
            'checkout_url' => rtrim((string) config('services.liqpay.base_url', 'https://www.liqpay.ua'), '/').'/api/3/checkout',
            'data' => $data,
            'signature' => $signature,
            'form_fields' => [
                'data' => $data,
                'signature' => $signature,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function capture(array $payload): array
    {
        return $this->apiRequest(array_merge($payload, ['action' => 'hold_completion']));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function refund(array $payload): array
    {
        return $this->apiRequest(array_merge($payload, ['action' => 'refund']));
    }

    /**
     * @param  array<string, mixed>  $headers
     */
    public function verifyWebhook(string $rawPayload, array $headers = []): bool
    {
        $parsed = $this->parseCallback($rawPayload);
        $data = $parsed['data'] ?? null;
        $signature = $parsed['signature'] ?? null;

        if (! is_string($data) || ! is_string($signature)) {
            return false;
        }

        return hash_equals($this->makeSignature($data), $signature);
    }

    /**
     * @param  array<string, mixed>  $headers
     * @return array<string, mixed>
     */
    public function normalizeWebhookEvent(string $rawPayload, array $headers = []): array
    {
        $parsed = $this->parseCallback($rawPayload);
        $event = $parsed['decoded'] ?? [];

        $status = strtolower((string) ($event['status'] ?? 'unknown'));
        $internalStatus = match ($status) {
            'success' => 'paid',
            'failure', 'error' => 'failed',
            'reversed', 'refunded' => 'refunded',
            default => 'pending',
        };

        return [
            'provider' => 'liqpay',
            'provider_payment_id' => (string) ($event['payment_id'] ?? $event['transaction_id'] ?? ''),
            'status' => $status,
            'internal_status' => $internalStatus,
            'order_id' => (string) ($event['order_id'] ?? ''),
            'amount_minor' => (int) round(((float) ($event['amount'] ?? 0)) * 100),
            'currency' => strtoupper((string) ($event['currency'] ?? 'UAH')),
            'raw' => $event,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function apiRequest(array $payload): array
    {
        $prepared = [
            'public_key' => $this->publicKey(),
            'version' => 3,
            'action' => (string) ($payload['action'] ?? ''),
            'order_id' => (string) ($payload['order_id'] ?? ''),
        ];

        if (array_key_exists('amount_minor', $payload)) {
            $prepared['amount'] = ((int) $payload['amount_minor']) / 100;
        } elseif (array_key_exists('amount', $payload)) {
            $prepared['amount'] = (float) $payload['amount'];
        }

        if (array_key_exists('currency', $payload)) {
            $prepared['currency'] = strtoupper((string) $payload['currency']);
        }

        if (array_key_exists('description', $payload)) {
            $prepared['description'] = (string) $payload['description'];
        }

        if (array_key_exists('transaction_id', $payload)) {
            $prepared['transaction_id'] = (string) $payload['transaction_id'];
        }

        $data = $this->encodePayload($prepared);
        $signature = $this->makeSignature($data);

        $response = Http::asForm()->post(
            rtrim((string) config('services.liqpay.base_url', 'https://www.liqpay.ua'), '/').'/api/request',
            [
                'data' => $data,
                'signature' => $signature,
            ]
        );

        /** @var array<string, mixed> $json */
        $json = $response->json() ?? [];

        return $json;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encodePayload(array $payload): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Unable to encode LiqPay payload.');
        }

        return base64_encode($json);
    }

    private function makeSignature(string $data): string
    {
        $privateKey = (string) config('services.liqpay.private_key');

        return base64_encode(sha1($privateKey.$data.$privateKey, true));
    }

    /**
     * @return array<string, mixed>
     */
    private function parseCallback(string $rawPayload): array
    {
        $parsed = [];
        parse_str($rawPayload, $parsed);

        if (! isset($parsed['data']) || ! is_string($parsed['data'])) {
            $json = json_decode($rawPayload, true);
            if (is_array($json)) {
                $parsed = $json;
            }
        }

        $decoded = [];
        if (isset($parsed['data']) && is_string($parsed['data'])) {
            $decodedJson = base64_decode($parsed['data'], true);
            if (is_string($decodedJson)) {
                $decoded = json_decode($decodedJson, true) ?: [];
            }
        }

        $parsed['decoded'] = is_array($decoded) ? $decoded : [];

        return $parsed;
    }

    private function publicKey(): string
    {
        return (string) config('services.liqpay.public_key');
    }
}
