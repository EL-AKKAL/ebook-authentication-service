<?php

use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = User::find(1);
    event(new UserRegistered($user));
});
