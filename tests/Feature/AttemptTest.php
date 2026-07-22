<?php

use App\Models\Attempt;
use App\Models\Exam;
use App\Notifications\AttemptOtpNotification;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('can request an otp for a published exam', function () {
    Notification::fake();

    $exam = Exam::factory()->create();

    $response = $this->postJson("/api/v1/public/exams/{$exam->slug}/attempts/request-otp", [
        'email' => 'taker@example.com',
    ]);

    $response->assertOk()->assertJson(['status' => true]);

    Notification::assertSentOnDemand(AttemptOtpNotification::class);
});

it('cannot request otp for a draft exam', function () {
    $exam = Exam::factory()->draft()->create();

    $response = $this->postJson("/api/v1/public/exams/{$exam->slug}/attempts/request-otp", [
        'email' => 'taker@example.com',
    ]);

    $response->assertNotFound();
});

it('creates an attempt after verifying otp', function () {
    $exam = Exam::factory()->create();
    $email = 'taker@example.com';

    $otp = (new OtpService("attempt:{$exam->id}"))->generate($email);

    $response = $this->postJson("/api/v1/public/exams/{$exam->slug}/attempts/verify-otp", [
        'email' => $email,
        'otp' => $otp,
        'name' => 'Taker Name',
    ]);

    $response->assertOk()->assertJson(['status' => true]);

    $this->assertDatabaseHas('attempts', [
        'exam_id' => $exam->id,
        'taker_email' => $email,
        'taker_name' => 'Taker Name',
    ]);
});

it('rejects an invalid otp during attempt verification', function () {
    $exam = Exam::factory()->create();

    $response = $this->postJson("/api/v1/public/exams/{$exam->slug}/attempts/verify-otp", [
        'email' => 'taker@example.com',
        'otp' => '000000',
        'name' => 'Taker Name',
    ]);

    $response->assertUnprocessable()->assertJsonValidationErrors('otp');
});

it('rejects otp verification if the exam is already completed', function () {
    $exam = Exam::factory()->create();
    $email = 'taker@example.com';

    Attempt::factory()->completed()->create([
        'exam_id' => $exam->id,
        'taker_email' => $email,
    ]);

    $otp = (new OtpService("attempt:{$exam->id}"))->generate($email);

    $response = $this->postJson("/api/v1/public/exams/{$exam->slug}/attempts/verify-otp", [
        'email' => $email,
        'otp' => $otp,
        'name' => 'Taker Name',
    ]);

    $response->assertUnprocessable()->assertJsonValidationErrors('email');
});
