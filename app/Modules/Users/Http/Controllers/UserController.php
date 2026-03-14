<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers;

use App\Core\Responses\SuccessResponse;
use App\Http\Controllers\Controller;
use App\Modules\Users\Http\Requests\DeleteUserRequest;
use App\Modules\Users\Http\Requests\StoreUserRequest;
use App\Modules\Users\Http\Requests\UpdateUserRequest;
use App\Modules\Users\Presentations\UserDestroyPresentation;
use App\Modules\Users\Presentations\UserIndexPresentation;
use App\Modules\Users\Presentations\UserShowPresentation;
use App\Modules\Users\Presentations\UserStorePresentation;
use App\Modules\Users\Presentations\UserUpdatePresentation;
use App\Modules\Users\Processors\UserDestroyProcessor;
use App\Modules\Users\Processors\UserIndexProcessor;
use App\Modules\Users\Processors\UserShowProcessor;
use App\Modules\Users\Processors\UserStoreProcessor;
use App\Modules\Users\Processors\UserUpdateProcessor;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function index(
        UserIndexProcessor $processor,
        UserIndexPresentation $presentation
    ): SuccessResponse {
        $users = $processor->execute();

        return new SuccessResponse(
            $presentation->present($users)
        );
    }

    public function show(
        UserShowProcessor $processor,
        UserShowPresentation $presentation,
        int $id
    ): SuccessResponse {
        $user = $processor->execute($id);

        return new SuccessResponse(
            $presentation->present($user)
        );
    }

    public function store(
        StoreUserRequest $request,
        UserStoreProcessor $processor,
        UserStorePresentation $presentation
    ): SuccessResponse {
        $user = $processor->execute($request);

        return new SuccessResponse(
            $presentation->present($user),
            ['message' => 'User was created successfully'],
            Response::HTTP_CREATED
        );
    }

    public function update(
        UpdateUserRequest $request,
        UserUpdateProcessor $processor,
        UserUpdatePresentation $presentation,
        int $id
    ): SuccessResponse {
        $user = $processor->execute($request, $id);

        return new SuccessResponse(
            $presentation->present($user),
            ['message' => 'User was updated successfully']
        );
    }

    public function destroy(
        DeleteUserRequest $request,
        UserDestroyProcessor $processor,
        UserDestroyPresentation $presentation,
        int $id
    ): SuccessResponse {
        $result = $processor->execute($request, $id);

        return new SuccessResponse(
            $presentation->present($result),
            ['message' => 'User was deleted successfully']
        );
    }
}
