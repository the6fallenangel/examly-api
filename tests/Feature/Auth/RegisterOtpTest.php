<?php

use App\Notifications\RegisterOtpNotification;
use Illuminate\Support\Facades\Notification;

it('can request a registration otp', function () {
    Notification::fake();

    $res = $this->postJson('/api/v1/auth/register/request-otp', ['email' => 'ali@example.com']);

    $res->assertOk()->assertJson([
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
