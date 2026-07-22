<?php

use App\Models\User;
use App\Notifications\RegisterOtpNotification;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('can request a registration otp', function () {
    Notification::fake();

    $res = $this->postJson('/api/v1/auth/register/request-otp', [
        'email' => 'ali@example.com',
    ]);

    $res->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
        ])
        ->assertJson([
            'status' => true,
            'message' => 'verification code sent successfully',
        ]);

    Notification::assertSentOnDemand(
        RegisterOtpNotification::class
    );
});

it('requires email to request otp', function () {
    $response = $this->postJson('/api/v1/auth/register/request-otp');

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('does not send otp for an existing email', function () {
    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $response = $this->postJson('/api/v1/auth/register/request-otp', [
        'email' => 'existing@example.com',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('creates a user with valid registration otp', function () {
    $email = 'ali@example.com';

    $otp = app(OtpService::class)
        ->generate($email);

    $response = $this->postJson('/api/v1/auth/register/verify-otp', [
        'email' => $email,
        'otp' => $otp,
        'name' => 'Ali',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user',
                'token',
            ],
        ])
        ->assertJson([
            'status' => true,
            'message' => 'account created successfully',
        ]);

    expect($response->json('data.token'))
        ->toBeString();

    $this->assertDatabaseHas('users', [
        'email' => $email,
        'name' => 'Ali',
    ]);
});

it('rejects invalid registration otp', function () {
    $response = $this->postJson('/api/v1/auth/register/verify-otp', [
        'email' => 'ali@example.com',
        'otp' => '123456',
        'name' => 'Ali',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('otp');
});

it('removes otp after successful registration', function () {
    $email = 'ali@example.com';

    $otpService = app(OtpService::class);

    $otp = $otpService->generate($email);

    $this->postJson('/api/v1/auth/register/verify-otp', [
        'email' => $email,
        'otp' => $otp,
        'name' => 'Ali',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertCreated();

    expect($otpService->verify($email, $otp))
        ->toBeFalse();
});

it('validates registration verification data', function () {
    $response = $this->postJson('/api/v1/auth/register/verify-otp');

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'email',
            'otp',
            'name',
            'password',
        ]);
});

it('can get authenticated user', function () {
    $user = User::factory()->create();

    $token = $user->createToken('test')->plainTextToken;

    $response = $this
        ->withToken($token)
        ->getJson('/api/v1/auth/me');

    $response
        ->assertOk()
        ->assertJson([
            'status' => true,
            'data' => [
                'email' => $user->email,
            ],
        ]);
});

it('cannot get authenticated user without token', function () {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertUnauthorized();
});

it('can login with valid credentials', function () {

    $user = User::factory()->create([
        'email' => 'ali@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'ali@example.com',
        'password' => 'password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user',
                'token',
            ],
        ])
        ->assertJson([
            'status' => true,
            'message' => 'logged in successfully',
        ]);

    expect($response->json('data.token'))
        ->toBeString();
});

it('cannot login with invalid credentials', function () {

    $user = User::factory()->create([
        'email' => 'ali@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'ali@example.com',
        'password' => 'wrong-password',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('requires email to login', function () {

    $response = $this->postJson('/api/v1/auth/login', [
        'password' => 'password123',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('requires password to login', function () {

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'ali@example.com',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});
