<?php

declare(strict_types=1);

namespace App\Modules\Payments\Repositories;

use App\Models\Payment;
use App\Modules\Payments\Contracts\Repositories\PaymentRepositoryInterface;
use App\Modules\Payments\DTO\CreatePaymentDTO;
use App\Modules\Payments\DTO\UpdatePaymentDTO;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function findById(int $id): ?Payment
    {
        /** @var Payment|null */
        return Payment::query()
            ->with(['subscription.user', 'subscription.plan'])
            ->find($id);
    }

    /**
     * @return Collection<int, Payment>
     */
    public function findAll(): Collection
    {
        return Payment::query()
            ->with(['subscription.user', 'subscription.plan'])
            ->get();
    }

    public function create(CreatePaymentDTO $dto): Payment
    {
        /** @var Payment $payment */
        $payment = Payment::query()->create($dto->toArray());
        $payment->load(['subscription.user', 'subscription.plan']);

        return $payment;
    }

    public function update(Payment $payment, UpdatePaymentDTO $dto): bool
    {
        return $payment->update($dto->toArray());
    }

    public function delete(Payment $payment): bool
    {
        return $payment->delete();
    }
}