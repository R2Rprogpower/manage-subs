<?php

declare(strict_types=1);

use App\Modules\Permissions\Http\Controllers\PermissionController;
use App\Modules\Permissions\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::get('/permissions/{id}', [PermissionController::class, 'show']);
    Route::post('/users/{userId}/permissions/{permissionId}', [PermissionController::class, 'assignToUser']);
    Route::post('/roles/{roleId}/permissions/{permissionId}', [PermissionController::class, 'assignToRole']);

    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::match(['put', 'patch'], '/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
    Route::post('/roles/{id}/permissions', [RoleController::class, 'assignPermissions']);
    Route::post('/users/{userId}/roles', [RoleController::class, 'assignRole']);
});
