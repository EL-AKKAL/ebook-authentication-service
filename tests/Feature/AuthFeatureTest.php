<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

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
