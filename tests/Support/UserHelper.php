<?php

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

function actingAsUser()
{
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    return [$user, "Bearer $token"];
}
