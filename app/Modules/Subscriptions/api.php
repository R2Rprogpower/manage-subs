<?php

declare(strict_types=1);

use App\Modules\Subscriptions\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/subscriptions/mine', [SubscriptionController::class, 'mine']);
    Route::post('/subscriptions/checkout', [SubscriptionController::class, 'checkout']);
    Route::post('/subscriptions/grant-free', [SubscriptionController::class, 'grantFree']);
    Route::post('/subscriptions/{id}/pending', [SubscriptionController::class, 'pending']);
    Route::post('/subscriptions/{id}/activate', [SubscriptionController::class, 'activate']);
    Route::post('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/subscriptions/{id}/suspend', [SubscriptionController::class, 'suspend']);
    Route::post('/subscriptions/{id}/renew', [SubscriptionController::class, 'renew']);
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show']);
    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::match(['put', 'patch'], '/subscriptions/{id}', [SubscriptionController::class, 'update']);
    Route::delete('/subscriptions/{id}', [SubscriptionController::class, 'destroy']);
});
