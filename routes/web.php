<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Ebook Authentication Service is running.',
        'api routes' => [
            '/refresh' => 'POST : refresh jwt token',
            '/login' => 'POST : login to your account',
            '/register' => 'POST : create a new account',
            '/forgot-password' => 'POST : ask for reset password',
            '/reset-password'=> 'POST : submit changed password',
            '/logout' => 'POST : kill session',
            '/me' => 'GET : get current auth user',
        ],
        'pipeline' => 'CI/CD pipeline is set up for automated tests and deployments.',
    ]);
});
