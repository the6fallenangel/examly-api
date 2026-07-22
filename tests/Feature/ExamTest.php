<?php

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('can create an exam', function () {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/exams', [
        'title' => 'Laravel Quiz',
        'description' => 'Laravel questions',
    ]);

    $response
        ->assertCreated()
        ->assertJson([
            'status' => true,
        ]);

    $this->assertDatabaseHas('exams', [
        'user_id' => $user->id,
        'title' => 'Laravel Quiz',
    ]);
});

it('can list user exams', function () {

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Exam::factory()
        ->count(3)
        ->create([
            'user_id' => $user->id,
        ]);

    $response = $this->getJson('/api/v1/exams');

    $response
        ->assertOk()
        ->assertJson([
            'status' => true,
        ]);
});

it('cannot update another users exam', function () {

    $user = User::factory()->create();

    $otherUser = User::factory()->create();

    $exam = Exam::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/v1/exams/{$exam->id}", [
        'title' => 'Hacked title',
    ]);

    $response->assertForbidden();
});

it('can update own exam', function () {

    $user = User::factory()->create();

    $exam = Exam::factory()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/v1/exams/{$exam->id}", [
        'title' => 'Updated Exam',
    ]);

    $response
        ->assertOk();

    $this->assertDatabaseHas('exams', [
        'id' => $exam->id,
        'title' => 'Updated Exam',
    ]);
});

it('can delete own exam', function () {

    $user = User::factory()->create();

    $exam = Exam::factory()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/exams/{$exam->id}");

    $response->assertOk();

    $this->assertDatabaseMissing('exams', [
        'id' => $exam->id,
    ]);
});
