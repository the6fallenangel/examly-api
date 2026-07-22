<?php

use App\Enums\QuestionType;
use App\Models\Exam;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('can create a question for own exam', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/exams/{$exam->id}/questions", [
        'type' => QuestionType::Text->value,
        'prompt' => 'What is Laravel?',
    ]);

    $response
        ->assertCreated()
        ->assertJson(['status' => true]);

    $this->assertDatabaseHas('questions', [
        'exam_id' => $exam->id,
        'prompt' => 'What is Laravel?',
    ]);
});

it('requires options for multiple choice questions', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/exams/{$exam->id}/questions", [
        'type' => QuestionType::MultipleChoice->value,
        'prompt' => 'Pick one',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('options');
});

it('cannot create a question for another users exam', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/exams/{$exam->id}/questions", [
        'type' => QuestionType::Text->value,
        'prompt' => 'Hacked question',
    ]);

    $response->assertForbidden();
});

it('can list exam questions', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);

    Question::factory()->count(3)->create(['exam_id' => $exam->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/exams/{$exam->id}/questions");

    $response
        ->assertOk()
        ->assertJson(['status' => true]);
});

it('can show a question', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);
    $question = Question::factory()->create(['exam_id' => $exam->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/exams/{$exam->id}/questions/{$question->id}");

    $response
        ->assertOk()
        ->assertJson([
            'status' => true,
            'data' => ['id' => $question->id],
        ]);
});

it('can update own question', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);
    $question = Question::factory()->create([
        'exam_id' => $exam->id,
        'type' => QuestionType::Text,
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/v1/exams/{$exam->id}/questions/{$question->id}", [
        'prompt' => 'Updated prompt',
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('questions', [
        'id' => $question->id,
        'prompt' => 'Updated prompt',
    ]);
});

it('cannot update a question on another users exam', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $otherUser->id]);
    $question = Question::factory()->create(['exam_id' => $exam->id]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/v1/exams/{$exam->id}/questions/{$question->id}", [
        'prompt' => 'Hacked prompt',
    ]);

    $response->assertForbidden();
});

it('can delete own question', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);
    $question = Question::factory()->create(['exam_id' => $exam->id]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/exams/{$exam->id}/questions/{$question->id}");

    $response->assertOk();

    $this->assertDatabaseMissing('questions', ['id' => $question->id]);
});
