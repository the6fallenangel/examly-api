<?php

use App\Models\User;
use App\Notifications\RegisterOtpNotification;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('can request a registration otp', function () {
    Notification::fake();

    $res = $this->postJson('/api/v1/auth/register/request-otp', ['email' => 'ali@example.com']);

    $res->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
        ])
        ->assertJson([
            'status' => true,
            'message' => 'Verification code sent successfully',
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

it('prevents authenticated users from requesting registration otp', function () {
    Sanctum::actingAs(
        User::factory()->create()
    );

    $response = $this->postJson('/api/v1/auth/register/request-otp', [
        'email' => 'new@example.com',
    ]);

    $response
        ->assertForbidden()
        ->assertJson([
            'status' => false,
            'message' => 'you are already authenticated',
        ]);
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
        ->assertOk()
        ->assertJson([
            'status' => true,
            'message' => 'account created successfully',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => $email,
        'name' => 'Ali',
    ]);

    expect(auth()->check())->toBeTrue();
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
        ->assertOk();

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
