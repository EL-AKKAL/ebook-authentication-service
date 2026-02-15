<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => ['message' => 'Auth service API served from api.php']);

Route::middleware('jwt.refresh')->post('/refresh', [AuthController::class, 'refresh']);

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::post('forgot-password', PasswordResetLinkController::class)
    ->name('password.request');

Route::post('reset-password', NewPasswordController::class)
    ->name('password.update');

Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/me', [AuthController::class, 'me'])->name('me');
});


  