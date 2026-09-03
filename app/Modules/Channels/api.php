<?php

declare(strict_types=1);

use App\Modules\Channels\Http\Controllers\ChannelController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/channels/available', [ChannelController::class, 'available']);
    Route::get('/channels', [ChannelController::class, 'index']);
    Route::post('/channels', [ChannelController::class, 'store']);
    Route::match(['put', 'patch'], '/channels/{id}', [ChannelController::class, 'update']);
    Route::delete('/channels/{id}', [ChannelController::class, 'destroy']);
});
