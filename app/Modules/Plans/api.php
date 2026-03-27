<?php

declare(strict_types=1);

use App\Modules\Plans\Http\Controllers\PlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/plans', [PlanController::class, 'index']);
    Route::get('/plans/{id}', [PlanController::class, 'show']);
    Route::post('/plans', [PlanController::class, 'store']);
    Route::match(['put', 'patch'], '/plans/{id}', [PlanController::class, 'update']);
    Route::delete('/plans/{id}', [PlanController::class, 'destroy']);
});
