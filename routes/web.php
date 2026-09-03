<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('admin.dashboard') : redirect()->route('login');
})->name('root');

Route::view('/login', 'auth-login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::view('/admin', 'admin-dashboard')
    ->middleware('auth')
    ->name('admin.dashboard');
