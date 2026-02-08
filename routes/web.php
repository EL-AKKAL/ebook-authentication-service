<?php

use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     $user = User::find(1);
//     event(new UserRegistered($user));
// });
Route::get('/', function () {
    return response()->json([
        'message' => 'Ebook Authentication Service is running.',
        // 'api routes' => [
        //     '/notifications' => 'Get all notifications',
        //     '/notifications/{id}/read' => 'Mark a notification as read',
        //     '/notifications/read-all' => 'Mark all notifications as read',
        // ],
        'pipeline' => 'CI/CD pipeline is set up for automated deployments.',
    ]);
});
