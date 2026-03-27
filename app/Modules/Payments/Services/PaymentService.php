<?php

declare(strict_types=1);

namespace App\Modules\Payments\Services;

use App\Models\Payment;
use App\Modules\Payments\Contracts\Repositories\PaymentRepositoryInterface;
use App\Modules\Payments\Contracts\Services\PaymentServiceInterface;
use App\Modules\Payments\DTO\CreatePaymentDTO;
use App\Modules\Payments\DTO\UpdatePaymentDTO;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;

class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository
    ) {}

    /**
     * @return Collection<int, Payment>
     */
    public function findAll(): Collection
    {
        return $this->paymentRepository->findAll();
    }

    public function findById(int $id): ?Payment
    {
        return $this->paymentRepository->findById($id);
    }

    public function create(CreatePaymentDTO $dto): Payment
    {
        return $this->paymentRepository->create($dto);
    }

    public function update(int $id, UpdatePaymentDTO $dto): Payment
    {
        $payment = $this->paymentRepository->findById($id);
        if (! $payment) {
            throw new \InvalidArgumentException("Payment with ID {$id} not found.");
        }

        $this->paymentRepository->update($payment, $dto);
        $payment->refresh();
        $payment->load(['subscription.user', 'subscription.plan']);

        return $payment;
    }

    public function delete(int $id): void
    {
        $payment = $this->paymentRepository->findById($id);
        if (! $payment) {
            throw new \InvalidArgumentException("Payment with ID {$id} not found.");
        }

        $this->paymentRepository->delete($payment);
    }

    public function markPaid(int $paymentId, ?DateTimeInterface $paidAt = null, ?int $actorId = null): Payment
    {
        throw new \BadMethodCallException('markPaid is not implemented yet.');
    }

    public function markFailed(int $paymentId, ?int $actorId = null): Payment
    {
        throw new \BadMethodCallException('markFailed is not implemented yet.');
    }

    public function onPaymentStateChanged(int $paymentId, ?int $actorId = null): void
    {
        throw new \BadMethodCallException('onPaymentStateChanged is not implemented yet.');
    }
}