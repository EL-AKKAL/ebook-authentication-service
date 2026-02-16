<?php

use App\Events\UserRegistered;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

uses(RefreshDatabase::class);

it('returns authentication service status', function () {
    $response = getJson('/');

    $response
        ->assertStatus(200)
        ->assertJson(
            [
                'message' => 'Ebook Authentication Service is running.'
            ]
        );
});

it('can register a new user', function () {

    $email = fake()->safeEmail();

    $response = postJson('/api/register', [
        'name' => 'John Doe',
        'email' => $email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'token_type' => 'bearer',
        ]);

    Event::assertDispatched(UserRegistered::class);

    expect(User::where('email', $email)
        ->exists())
        ->toBeTrue();
});

it('fails registration with invalid credentials', function () {

    $response = postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password123',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    $response = postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('fails registration if email already exists', function () {

    $email = fake()->safeEmail();

    User::factory()->create([
        'email' => $email,
    ]);

    $response = postJson('/api/register', [
        'name' => 'John Doe',
        'email' => $email,
        'password' => 'password123',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
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
        ->assertJson([
            'token_type' => 'bearer',
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
    [$user, $token] = actingAsUser();

    $response = withHeader('Authorization', $token)
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

    [$user, $token] = actingAsUser();

    $response = withHeader('Authorization', $token)
        ->postJson('/api/logout');

    $response
        ->assertStatus(200)
        ->assertJson([
            'message' => 'Successfully logged out'
        ]);
});

it('can refresh jwt token', function () {
    [$user, $token] = actingAsUser();

    $response = withHeader('Authorization', $token)
        ->postJson('/api/refresh');

    $response
        ->assertStatus(200)
        ->assertJson([
            'token_type' => 'bearer',
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
        ResetPasswordNotification::class
    );
});

it('validates email on forgot password', function () {
    postJson('/api/forgot-password', [
        'email' => 'invalid-email',
    ])->assertStatus(422);
});

it('can reset password with valid token', function () {
    Event::fake();

    $password = 'password';

    $user = User::factory()->create([
        'password' => bcrypt('old-password'),
    ]);

    $token = Password::createToken($user);

    $response = postJson('/api/reset-password', [
        'email' => $user->email,
        'token' => $token,
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    $response
        ->assertStatus(200)
        ->assertJsonStructure(['status']);

    expect(Hash::check($password, $user->fresh()->password))
        ->toBeTrue();

    Event::assertDispatched(PasswordReset::class);
});

it('fails reset with invalid token', function () {
    $user = User::factory()->create();

    $response = postJson('/api/reset-password', [
        'email' => $user->email,
        'token' => 'invalid-token',
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonStructure(['email']);
});

it('fails if password confirmation does not match', function () {
    $user = User::factory()->create();

    $token = Password::createToken($user);

    $response = postJson('/api/reset-password', [
        'email' => $user->email,
        'token' => $token,
        'password' => 'new-password123',
        'password_confirmation' => 'wrong-confirmation',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});
