<?php

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns a clean 404 response for unknown routes', function () {
    $response = $this->getJson('/api/v1/does-not-exist');

    $response
        ->assertNotFound()
        ->assertJson([
            'status' => false,
            'message' => 'resource not found',
        ]);
});

it('returns a clean 404 response when a route-bound model is missing', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/exams/{$exam->id}/attempts/999999");

    $response
        ->assertNotFound()
        ->assertJson([
            'status' => false,
            'message' => 'resource not found',
        ]);
});

it('returns a clean 401 response when unauthenticated', function () {
    $response = $this->getJson('/api/v1/auth/me');

    $response
        ->assertUnauthorized()
        ->assertJson([
            'status' => false,
            'message' => 'unauthenticated',
        ]);
});

it('returns a clean 403 response for unauthorized actions', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/v1/exams/{$exam->id}", ['title' => 'Hacked']);

    $response
        ->assertForbidden()
        ->assertJson(['status' => false]);
});

it('returns a clean 422 response with validation errors', function () {
    $response = $this->postJson('/api/v1/auth/login', []);

    $response
        ->assertUnprocessable()
        ->assertJson(['status' => false])
        ->assertJsonValidationErrors(['email', 'password']);
});
