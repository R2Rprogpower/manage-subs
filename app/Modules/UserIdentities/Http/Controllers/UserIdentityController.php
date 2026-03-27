<?php

declare(strict_types=1);

namespace App\Modules\UserIdentities\Http\Controllers;

use App\Core\Responses\SuccessResponse;
use App\Http\Controllers\Controller;
use App\Modules\UserIdentities\Http\Requests\DeleteUserIdentityRequest;
use App\Modules\UserIdentities\Http\Requests\StoreUserIdentityRequest;
use App\Modules\UserIdentities\Http\Requests\UpdateUserIdentityRequest;
use App\Modules\UserIdentities\Presentations\UserIdentityDestroyPresentation;
use App\Modules\UserIdentities\Presentations\UserIdentityIndexPresentation;
use App\Modules\UserIdentities\Presentations\UserIdentityPresentation;
use App\Modules\UserIdentities\Processors\UserIdentityDestroyProcessor;
use App\Modules\UserIdentities\Processors\UserIdentityIndexProcessor;
use App\Modules\UserIdentities\Processors\UserIdentityShowProcessor;
use App\Modules\UserIdentities\Processors\UserIdentityStoreProcessor;
use App\Modules\UserIdentities\Processors\UserIdentityUpdateProcessor;
use Illuminate\Http\Response;

class UserIdentityController extends Controller
{
    public function index(
        UserIdentityIndexProcessor $processor,
        UserIdentityIndexPresentation $presentation
    ): SuccessResponse {
        return new SuccessResponse($presentation->present($processor->execute()));
    }

    public function show(
        UserIdentityShowProcessor $processor,
        UserIdentityPresentation $presentation,
        int $id
    ): SuccessResponse {
        return new SuccessResponse($presentation->present($processor->execute($id)));
    }

    public function store(
        StoreUserIdentityRequest $request,
        UserIdentityStoreProcessor $processor,
        UserIdentityPresentation $presentation
    ): SuccessResponse {
        return new SuccessResponse(
            $presentation->present($processor->execute($request)),
            ['message' => 'User identity was created successfully'],
            Response::HTTP_CREATED
        );
    }

    public function update(
        UpdateUserIdentityRequest $request,
        UserIdentityUpdateProcessor $processor,
        UserIdentityPresentation $presentation,
        int $id
    ): SuccessResponse {
        return new SuccessResponse(
            $presentation->present($processor->execute($request, $id)),
            ['message' => 'User identity was updated successfully']
        );
    }

    public function destroy(
        DeleteUserIdentityRequest $request,
        UserIdentityDestroyProcessor $processor,
        UserIdentityDestroyPresentation $presentation,
        int $id
    ): SuccessResponse {
        return new SuccessResponse(
            $presentation->present($processor->execute($request, $id)),
            ['message' => 'User identity was deleted successfully']
        );
    }
}
