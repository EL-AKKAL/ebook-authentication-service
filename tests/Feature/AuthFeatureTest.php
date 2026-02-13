<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\Fluent\AssertableJson;
use Tymon\JWTAuth\Facades\JWTAuth;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

uses(RefreshDatabase::class);

it('returns authentication service status', function () {
    $response = getJson('/');

    $response
        ->assertStatus(200)
        ->assertJson(
            fn(AssertableJson $json) =>
            $json->has('api routes')
                ->etc()
        );
});

it('can register a new user', function () {

    $email = 'john@example.com';

    $response = postJson('/api/register', [
        'name' => 'John Doe',
        'email' => $email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'name',
            'email',
            'id',
        ]);

    expect(User::where('email', $email)
        ->exists())
        ->toBeTrue();
});

it('can login with valid credentials', function () {

    $password = 'password123';

    $user = User::factory()->create([
        'password' => bcrypt($password),
    ]);

    $response = postJson('/api/login', [
        'email' => $user->email,
        'password' => $password,
    ]);

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
        ]);
});

it('fails login with invalid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $response = postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response
        ->assertStatus(401)
        ->assertJson([
            'error' => 'Unauthorized'
        ]);
});

it('returns authenticated user data', function () {
    $user = User::factory()->create();

    $token = JWTAuth::fromUser($user);

    $response = withHeader('Authorization', "Bearer $token")
        ->getJson('/api/me');

    $response
        ->assertStatus(200)
        ->assertJson([
            'id' => $user->id,
            'email' => $user->email,
        ]);
});

it('blocks /me without token', function () {
    getJson('/api/me')
        ->assertStatus(401);
});


it('can logout authenticated user', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = withHeader('Authorization', "Bearer $token")
        ->postJson('/api/logout');

    $response
        ->assertStatus(200)
        ->assertJson([
            'message' => 'Successfully logged out'
        ]);
});

it('can refresh jwt token', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = withHeader('Authorization', "Bearer $token")
        ->postJson('/api/refresh');

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
        ]);
});

it('sends password reset link', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = postJson('/api/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertStatus(200);
    Notification::assertSentTo(
        $user,
        ResetPassword::class
    );
});

it('validates email on forgot password', function () {
    postJson('/api/forgot-password', [
        'email' => 'invalid-email',
    ])->assertStatus(422);
});
