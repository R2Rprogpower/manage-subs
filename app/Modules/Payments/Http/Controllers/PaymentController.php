<?php

declare(strict_types=1);

namespace App\Modules\Payments\Http\Controllers;

use App\Core\Responses\SuccessResponse;
use App\Http\Controllers\Controller;
use App\Modules\Payments\Http\Requests\DeletePaymentRequest;
use App\Modules\Payments\Http\Requests\StorePaymentRequest;
use App\Modules\Payments\Http\Requests\UpdatePaymentRequest;
use App\Modules\Payments\Presentations\PaymentDestroyPresentation;
use App\Modules\Payments\Presentations\PaymentIndexPresentation;
use App\Modules\Payments\Presentations\PaymentPresentation;
use App\Modules\Payments\Processors\PaymentDestroyProcessor;
use App\Modules\Payments\Processors\PaymentIndexProcessor;
use App\Modules\Payments\Processors\PaymentShowProcessor;
use App\Modules\Payments\Processors\PaymentStoreProcessor;
use App\Modules\Payments\Processors\PaymentUpdateProcessor;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function index(
        PaymentIndexProcessor $processor,
        PaymentIndexPresentation $presentation
    ): SuccessResponse {
        return new SuccessResponse($presentation->present($processor->execute()));
    }

    public function show(
        PaymentShowProcessor $processor,
        PaymentPresentation $presentation,
        int $id
    ): SuccessResponse {
        return new SuccessResponse($presentation->present($processor->execute($id)));
    }

    public function store(
        StorePaymentRequest $request,
        PaymentStoreProcessor $processor,
        PaymentPresentation $presentation
    ): SuccessResponse {
        return new SuccessResponse(
            $presentation->present($processor->execute($request)),
            ['message' => 'Payment was created successfully'],
            Response::HTTP_CREATED
        );
    }

    public function update(
        UpdatePaymentRequest $request,
        PaymentUpdateProcessor $processor,
        PaymentPresentation $presentation,
        int $id
    ): SuccessResponse {
        return new SuccessResponse(
            $presentation->present($processor->execute($request, $id)),
            ['message' => 'Payment was updated successfully']
        );
    }

    public function destroy(
        DeletePaymentRequest $request,
        PaymentDestroyProcessor $processor,
        PaymentDestroyPresentation $presentation,
        int $id
    ): SuccessResponse {
        return new SuccessResponse(
            $presentation->present($processor->execute($request, $id)),
            ['message' => 'Payment was deleted successfully']
        );
    }
}
