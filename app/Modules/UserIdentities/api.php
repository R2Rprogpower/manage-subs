<?php

declare(strict_types=1);

use App\Modules\UserIdentities\Http\Controllers\UserIdentityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user-identities', [UserIdentityController::class, 'index']);
    Route::get('/user-identities/{id}', [UserIdentityController::class, 'show']);
    Route::post('/user-identities', [UserIdentityController::class, 'store']);
    Route::match(['put', 'patch'], '/user-identities/{id}', [UserIdentityController::class, 'update']);
    Route::delete('/user-identities/{id}', [UserIdentityController::class, 'destroy']);
});
