<?php

declare(strict_types=1);

use App\Modules\Payments\Http\Controllers\PaymentController;
use App\Modules\Payments\Http\Controllers\PaymentCheckoutController;
use App\Modules\Payments\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::post('/payments/checkout/liqpay', [PaymentCheckoutController::class, 'liqpay']);
    Route::match(['put', 'patch'], '/payments/{id}', [PaymentController::class, 'update']);
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);
});

Route::post('/payments/webhooks/liqpay', [PaymentWebhookController::class, 'liqpay']);