<?php

declare(strict_types=1);

namespace App\Modules\Payments\Contracts\Repositories;

use App\Models\Payment;
use App\Modules\Payments\DTO\CreatePaymentDTO;
use App\Modules\Payments\DTO\UpdatePaymentDTO;
use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface
{
    public function findById(int $id): ?Payment;

    public function findByProviderPaymentId(string $provider, string $providerPaymentId): ?Payment;

    /**
     * @return Collection<int, Payment>
     */
    public function findAll(): Collection;

    public function create(CreatePaymentDTO $dto): Payment;

    public function update(Payment $payment, UpdatePaymentDTO $dto): bool;

    public function delete(Payment $payment): bool;
}
