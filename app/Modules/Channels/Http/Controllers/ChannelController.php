<?php

declare(strict_types=1);

namespace App\Modules\Channels\Http\Controllers;

use App\Core\Responses\SuccessResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Channels\Http\Requests\DeleteChannelRequest;
use App\Modules\Channels\Http\Requests\StoreChannelRequest;
use App\Modules\Channels\Http\Requests\UpdateChannelRequest;
use App\Modules\Channels\Presentations\ChannelPresentation;
use App\Modules\Channels\Processors\AvailableChannelIndexProcessor;
use App\Modules\Channels\Processors\ChannelDestroyProcessor;
use App\Modules\Channels\Processors\ChannelIndexProcessor;
use App\Modules\Channels\Processors\ChannelStoreProcessor;
use App\Modules\Channels\Processors\ChannelUpdateProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChannelController extends Controller
{
    public function index(Request $request, ChannelIndexProcessor $processor, ChannelPresentation $presentation): SuccessResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        return new SuccessResponse($presentation->present($processor->execute($user)));
    }

    public function available(AvailableChannelIndexProcessor $processor, ChannelPresentation $presentation): SuccessResponse
    {
        return new SuccessResponse($presentation->present($processor->execute()));
    }

    public function store(StoreChannelRequest $request, ChannelStoreProcessor $processor, ChannelPresentation $presentation): SuccessResponse
    {
        return new SuccessResponse($presentation->present($processor->execute($request)), ['message' => 'Channel was created successfully'], Response::HTTP_CREATED);
    }

    public function update(UpdateChannelRequest $request, ChannelUpdateProcessor $processor, ChannelPresentation $presentation, int $id): SuccessResponse
    {
        return new SuccessResponse($presentation->present($processor->execute($request, $id)), ['message' => 'Channel was updated successfully']);
    }

    public function destroy(DeleteChannelRequest $request, ChannelDestroyProcessor $processor, int $id): SuccessResponse
    {
        return new SuccessResponse(['success' => $processor->execute($id)], ['message' => 'Channel was deleted successfully']);
    }
}
