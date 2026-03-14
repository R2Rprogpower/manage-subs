<?php

declare(strict_types=1);

use App\Modules\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/mfa/setup', [AuthController::class, 'mfaSetup']);
    Route::post('/mfa/verify', [AuthController::class, 'mfaVerify']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/tokens/revoke', [AuthController::class, 'revokeToken']);
    });
});
