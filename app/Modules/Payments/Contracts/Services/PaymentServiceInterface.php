<?php

declare(strict_types=1);

namespace App\Modules\Payments\Contracts\Services;

use App\Models\Payment;
use App\Modules\Payments\DTO\CreatePaymentDTO;
use App\Modules\Payments\DTO\UpdatePaymentDTO;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;

interface PaymentServiceInterface
{
    /**
     * @return Collection<int, Payment>
     */
    public function findAll(): Collection;

    public function findById(int $id): ?Payment;

    public function create(CreatePaymentDTO $dto): Payment;

    public function update(int $id, UpdatePaymentDTO $dto): Payment;

    public function delete(int $id): void;

    public function markPaid(int $paymentId, ?DateTimeInterface $paidAt = null, ?int $actorId = null): Payment;

    public function markFailed(int $paymentId, ?int $actorId = null): Payment;

    public function onPaymentStateChanged(int $paymentId, ?int $actorId = null): void;
}
