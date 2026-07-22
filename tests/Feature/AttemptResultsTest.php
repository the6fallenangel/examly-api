<?php

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('exam owner can list verified attempts', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);

    Attempt::factory()->verified()->count(2)->create(['exam_id' => $exam->id]);
    Attempt::factory()->create(['exam_id' => $exam->id]); // unverified, should be excluded

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/exams/{$exam->id}/attempts");

    $response->assertOk()->assertJson(['status' => true]);
    expect($response->json('data.pagination.total'))->toBe(2);
});

it('cannot list attempts of another users exam', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/exams/{$exam->id}/attempts");

    $response->assertForbidden();
});

it('exam owner can view attempt details with answers', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);
    $question = Question::factory()->type(QuestionType::Text)->create(['exam_id' => $exam->id]);
    $attempt = Attempt::factory()->verified()->create(['exam_id' => $exam->id]);

    Answer::factory()->create([
        'attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'response' => 'my answer',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/exams/{$exam->id}/attempts/{$attempt->id}");

    $response
        ->assertOk()
        ->assertJson([
            'status' => true,
            'data' => ['id' => $attempt->id],
        ]);

    expect($response->json('data.answers.0.question_prompt'))->toBe($question->prompt);
});

it('returns 404 when attempt does not belong to the exam', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);
    $otherExam = Exam::factory()->create(['user_id' => $user->id]);
    $attempt = Attempt::factory()->verified()->create(['exam_id' => $otherExam->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/exams/{$exam->id}/attempts/{$attempt->id}");

    $response->assertNotFound();
});

it('exam owner can download an uploaded answer file', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);
    $question = Question::factory()->type(QuestionType::FileUpload)->create(['exam_id' => $exam->id]);
    $attempt = Attempt::factory()->verified()->create(['exam_id' => $exam->id]);

    $path = "attempts/{$attempt->id}/{$question->id}/resume.pdf";
    Storage::disk('local')->put($path, 'fake-content');

    $answer = Answer::factory()->create([
        'attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'response' => $path,
    ]);

    Sanctum::actingAs($user);

    $response = $this->get("/api/v1/exams/{$exam->id}/attempts/{$attempt->id}/answers/{$answer->id}/download");

    $response->assertOk();
});

it('cannot download a file from another users exam attempt', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $otherUser->id]);
    $question = Question::factory()->type(QuestionType::FileUpload)->create(['exam_id' => $exam->id]);
    $attempt = Attempt::factory()->verified()->create(['exam_id' => $exam->id]);

    $path = "attempts/{$attempt->id}/{$question->id}/resume.pdf";
    Storage::disk('local')->put($path, 'fake-content');

    $answer = Answer::factory()->create([
        'attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'response' => $path,
    ]);

    Sanctum::actingAs($user);

    $response = $this->get("/api/v1/exams/{$exam->id}/attempts/{$attempt->id}/answers/{$answer->id}/download");

    $response->assertForbidden();
});
