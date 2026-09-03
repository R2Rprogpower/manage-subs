<?php

declare(strict_types=1);

namespace App\Modules\Subscriptions\Http\Controllers;

use App\Core\Responses\SuccessResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Subscriptions\Http\Requests\CheckoutSubscriptionRequest;
use App\Modules\Subscriptions\Http\Requests\DeleteSubscriptionRequest;
use App\Modules\Subscriptions\Http\Requests\GrantFreeSubscriptionRequest;
use App\Modules\Subscriptions\Http\Requests\ManageSubscriptionRequest;
use App\Modules\Subscriptions\Http\Requests\StoreSubscriptionRequest;
use App\Modules\Subscriptions\Http\Requests\UpdateSubscriptionRequest;
use App\Modules\Subscriptions\Http\Requests\ViewSubscriptionRequest;
use App\Modules\Subscriptions\Presentations\SubscriptionDestroyPresentation;
use App\Modules\Subscriptions\Presentations\SubscriptionIndexPresentation;
use App\Modules\Subscriptions\Presentations\SubscriptionPresentation;
use App\Modules\Subscriptions\Processors\GrantFreeSubscriptionProcessor;
use App\Modules\Subscriptions\Processors\SubscriptionCheckoutProcessor;
use App\Modules\Subscriptions\Processors\SubscriptionDestroyProcessor;
use App\Modules\Subscriptions\Processors\SubscriptionIndexProcessor;
use App\Modules\Subscriptions\Processors\SubscriptionLifecycleProcessor;
use App\Modules\Subscriptions\Processors\SubscriptionMineProcessor;
use App\Modules\Subscriptions\Processors\SubscriptionShowProcessor;
use App\Modules\Subscriptions\Processors\SubscriptionStoreProcessor;
use App\Modules\Subscriptions\Processors\SubscriptionUpdateProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubscriptionController extends Controller
{
    public function mine(
        Request $request,
        SubscriptionMineProcessor $processor,
        SubscriptionIndexPresentation $presentation
    ): SuccessResponse {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        return new SuccessResponse($presentation->present($processor->execute($user->id)));
    }

    public function checkout(
        CheckoutSubscriptionRequest $request,
        SubscriptionCheckoutProcessor $processor,
        SubscriptionPresentation $presentation
    ): SuccessResponse {
        return new SuccessResponse(
            $presentation->present($processor->execute($request)),
            ['message' => 'Placeholder confirmed. No money was charged.'],
            Response::HTTP_CREATED,
        );
    }

    public function index(
        ViewSubscriptionRequest $request,
        SubscriptionIndexProcessor $processor,
        SubscriptionIndexPresentation $presentation
    ): SuccessResponse {
        return new SuccessResponse($presentation->present($processor->execute()));
    }

    public function show(
        ViewSubscriptionRequest $request,
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

    public function activate(ManageSubscriptionRequest $request, SubscriptionLifecycleProcessor $processor, SubscriptionPresentation $presentation, int $id): SuccessResponse
    {
        return new SuccessResponse($presentation->present($processor->activate($request, $id)), ['message' => 'Subscription was activated successfully']);
    }

    public function cancel(ManageSubscriptionRequest $request, SubscriptionLifecycleProcessor $processor, SubscriptionPresentation $presentation, int $id): SuccessResponse
    {
        return new SuccessResponse($presentation->present($processor->cancel($request, $id)), ['message' => 'Subscription was cancelled successfully']);
    }

    public function renew(ManageSubscriptionRequest $request, SubscriptionLifecycleProcessor $processor, SubscriptionPresentation $presentation, int $id): SuccessResponse
    {
        return new SuccessResponse($presentation->present($processor->renew($request, $id)), ['message' => 'Subscription was renewed successfully']);
    }

    public function grantFree(GrantFreeSubscriptionRequest $request, GrantFreeSubscriptionProcessor $processor, SubscriptionPresentation $presentation): SuccessResponse
    {
        return new SuccessResponse($presentation->present($processor->execute($request)), ['message' => 'Free access was granted successfully'], Response::HTTP_CREATED);
    }
}
