<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use Tymon\JWTAuth\Facades\JWTAuth;
class AuthController extends Controller
{
    public function login()
    {
        $credentials = request(['email', 'password']);

        JWTAuth::factory()->setTTL(config('jwt.ttl'));

        if (!$token = JWTAuth::attempt($credentials))
            return response()->json(['error' => 'Unauthorized'], 401);

        return $this->respondWithToken($token, JWTAuth::user());
    }

    public function register()
    {
        $data = request()->only(['name', 'email', 'password']);

        $data['password'] = bcrypt($data['password']);

        $user = \App\Models\User::create($data);

        JWTAuth::factory()->setTTL(config('jwt.ttl'));
        $token = JWTAuth::fromUser($user);

        event(new UserRegistered($user));

        return $this->respondWithToken($token, $user);
    }

    public function me()
    {
        return response()->json(JWTAuth::user());
    }

    public function logout()
    {
        JWTAuth::parseToken()->invalidate();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        $newToken = JWTAuth::parseToken()->refresh();
        return $this->respondWithToken($newToken);
    }

    protected function respondWithToken($token, $user = null)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'name' => $user?->name,
            'email' => $user?->email,
            'id' => $user?->id,
        ]);
    }
}
