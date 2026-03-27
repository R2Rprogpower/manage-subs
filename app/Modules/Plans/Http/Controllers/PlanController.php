<?php

declare(strict_types=1);

namespace App\Modules\Plans\Http\Controllers;

use App\Core\Responses\SuccessResponse;
use App\Http\Controllers\Controller;
use App\Modules\Plans\Http\Requests\DeletePlanRequest;
use App\Modules\Plans\Http\Requests\StorePlanRequest;
use App\Modules\Plans\Http\Requests\UpdatePlanRequest;
use App\Modules\Plans\Presentations\PlanDestroyPresentation;
use App\Modules\Plans\Presentations\PlanIndexPresentation;
use App\Modules\Plans\Presentations\PlanPresentation;
use App\Modules\Plans\Processors\PlanDestroyProcessor;
use App\Modules\Plans\Processors\PlanIndexProcessor;
use App\Modules\Plans\Processors\PlanShowProcessor;
use App\Modules\Plans\Processors\PlanStoreProcessor;
use App\Modules\Plans\Processors\PlanUpdateProcessor;
use Illuminate\Http\Response;

class PlanController extends Controller
{
    public function index(
        PlanIndexProcessor $processor,
        PlanIndexPresentation $presentation
    ): SuccessResponse {
        return new SuccessResponse($presentation->present($processor->execute()));
    }

    public function show(
        PlanShowProcessor $processor,
        PlanPresentation $presentation,
        int $id
    ): SuccessResponse {
        return new SuccessResponse($presentation->present($processor->execute($id)));
    }

    public function store(
        StorePlanRequest $request,
        PlanStoreProcessor $processor,
        PlanPresentation $presentation
    ): SuccessResponse {
        return new SuccessResponse(
            $presentation->present($processor->execute($request)),
            ['message' => 'Plan was created successfully'],
            Response::HTTP_CREATED
        );
    }

    public function update(
        UpdatePlanRequest $request,
        PlanUpdateProcessor $processor,
        PlanPresentation $presentation,
        int $id
    ): SuccessResponse {
        return new SuccessResponse(
            $presentation->present($processor->execute($request, $id)),
            ['message' => 'Plan was updated successfully']
        );
    }

    public function destroy(
        DeletePlanRequest $request,
        PlanDestroyProcessor $processor,
        PlanDestroyPresentation $presentation,
        int $id
    ): SuccessResponse {
        return new SuccessResponse(
            $presentation->present($processor->execute($request, $id)),
            ['message' => 'Plan was deleted successfully']
        );
    }
}
