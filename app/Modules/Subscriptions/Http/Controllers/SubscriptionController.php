<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Http\Controllers;

use App\Core\Responses\SuccessResponse;
use App\Http\Controllers\Controller;
use App\Modules\Subscriptions\Http\Requests\DeleteSubscriptionRequest;
use App\Modules\Subscriptions\Http\Requests\StoreSubscriptionRequest;
use App\Modules\Subscriptions\Http\Requests\UpdateSubscriptionRequest;
use App\Modules\Subscriptions\Presentations\SubscriptionDestroyPresentation;
use App\Modules\Subscriptions\Presentations\SubscriptionIndexPresentation;
use App\Modules\Subscriptions\Presentations\SubscriptionPresentation;
use App\Modules\Subscriptions\Processors\SubscriptionDestroyProcessor;
use App\Modules\Subscriptions\Processors\SubscriptionIndexProcessor;
use App\Modules\Subscriptions\Processors\SubscriptionShowProcessor;
use App\Modules\Subscriptions\Processors\SubscriptionStoreProcessor;
use App\Modules\Subscriptions\Processors\SubscriptionUpdateProcessor;
use Illuminate\Http\Response;

class SubscriptionController extends Controller
{
    public function index(
        SubscriptionIndexProcessor $processor,
        SubscriptionIndexPresentation $presentation
    ): SuccessResponse {
        return new SuccessResponse($presentation->present($processor->execute()));
    }

    public function show(
        SubscriptionShowProcessor $processor,
        SubscriptionPresentation $presentation,
        int $id
    ): SuccessResponse {
        return new SuccessResponse($presentation->present($processor->execute($id)));
    }

    public function store(
        StoreSubscriptionRequest $request,
        SubscriptionStoreProcessor $processor,
        SubscriptionPresentation $presentation
    ): SuccessResponse {
        return new SuccessResponse(
            $presentation->present($processor->execute($request)),
            ['message' => 'Subscription was created successfully'],
            Response::HTTP_CREATED
        );
    }

    public function update(
        UpdateSubscriptionRequest $request,
        SubscriptionUpdateProcessor $processor,
        SubscriptionPresentation $presentation,
        int $id
    ): SuccessResponse {
        return new SuccessResponse(
            $presentation->present($processor->execute($request, $id)),
            ['message' => 'Subscription was updated successfully']
        );
    }

    public function destroy(
        DeleteSubscriptionRequest $request,
        SubscriptionDestroyProcessor $processor,
        SubscriptionDestroyPresentation $presentation,
        int $id
    ): SuccessResponse {
        return new SuccessResponse(
            $presentation->present($processor->execute($request, $id)),
            ['message' => 'Subscription was deleted successfully']
        );
    }
}
