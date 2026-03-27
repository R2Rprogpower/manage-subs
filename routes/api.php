<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/health', static function (): \Illuminate\Http\JsonResponse {
    DB::connection()->getPdo(); // throws if DB is unreachable

    return response()->json(['status' => 'ok !!! manage subs']);
});

require base_path('app/Modules/Auth/api.php');
require base_path('app/Modules/Permissions/api.php');
require base_path('app/Modules/Users/api.php');
require base_path('app/Modules/UserIdentities/api.php');
require base_path('app/Modules/Plans/api.php');
require base_path('app/Modules/Subscriptions/api.php');
require base_path('app/Modules/Payments/api.php');
