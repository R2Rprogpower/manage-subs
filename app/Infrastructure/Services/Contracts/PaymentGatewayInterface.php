<?php

declare(strict_types=1);

namespace App\Infrastructure\Services\Contracts;

interface PaymentGatewayInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createCheckout(array $payload): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function capture(array $payload): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function refund(array $payload): array;

    /**
     * @param  array<string, mixed>  $headers
     */
    public function verifyWebhook(string $rawPayload, array $headers = []): bool;

    /**
     * @param  array<string, mixed>  $headers
     * @return array<string, mixed>
     */
    public function normalizeWebhookEvent(string $rawPayload, array $headers = []): array;
}