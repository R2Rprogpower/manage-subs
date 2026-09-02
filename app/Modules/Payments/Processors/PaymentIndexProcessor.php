<?php

declare(strict_types=1);

namespace App\Modules\Payments\Processors;

use App\Core\Abstracts\Processor;
use App\Models\Payment;
use App\Modules\Payments\Contracts\Services\PaymentServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class PaymentIndexProcessor extends Processor
{
    public function __construct(
        private readonly PaymentServiceInterface $paymentService
    ) {}

    /**
     * @return Collection<int, Payment>
     */
    public function execute(): Collection
    {
        return $this->paymentService->findAll();
    }
}
