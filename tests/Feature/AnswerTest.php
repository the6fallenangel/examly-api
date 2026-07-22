<?php

use App\Enums\QuestionType;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('can submit a text answer for a verified attempt', function () {
    $exam = Exam::factory()->create();
    $question = Question::factory()->type(QuestionType::Text)->create(['exam_id' => $exam->id]);
    $attempt = Attempt::factory()->verified()->create(['exam_id' => $exam->id]);

    $response = $this->postJson(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/questions/{$question->id}/answer",
        ['response' => 'my answer'],
        ['X-Attempt-Token' => $attempt->token]
    );

    $response->assertOk()->assertJson(['status' => true]);

    $this->assertDatabaseHas('answers', [
        'attempt_id' => $attempt->id,
        'question_id' => $question->id,
    ]);
});

it('rejects an invalid option for multiple choice questions', function () {
    $exam = Exam::factory()->create();
    $question = Question::factory()->type(QuestionType::MultipleChoice)->create([
        'exam_id' => $exam->id,
        'options' => ['a', 'b', 'c'],
    ]);
    $attempt = Attempt::factory()->verified()->create(['exam_id' => $exam->id]);

    $response = $this->postJson(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/questions/{$question->id}/answer",
        ['response' => 'not-an-option'],
        ['X-Attempt-Token' => $attempt->token]
    );

    $response->assertUnprocessable()->assertJsonValidationErrors('response');
});

it('rejects submission with an invalid attempt token', function () {
    $exam = Exam::factory()->create();
    $question = Question::factory()->type(QuestionType::Text)->create(['exam_id' => $exam->id]);
    $attempt = Attempt::factory()->verified()->create(['exam_id' => $exam->id]);

    $response = $this->postJson(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/questions/{$question->id}/answer",
        ['response' => 'my answer'],
        ['X-Attempt-Token' => 'wrong-token']
    );

    $response->assertForbidden();
});

it('rejects submission for an unverified attempt', function () {
    $exam = Exam::factory()->create();
    $question = Question::factory()->type(QuestionType::Text)->create(['exam_id' => $exam->id]);
    $attempt = Attempt::factory()->create(['exam_id' => $exam->id]);

    $response = $this->postJson(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/questions/{$question->id}/answer",
        ['response' => 'my answer'],
        ['X-Attempt-Token' => $attempt->token]
    );

    $response->assertForbidden();
});

it('can upload a file for a file upload question', function () {
    Storage::fake('local');

    $exam = Exam::factory()->create();
    $question = Question::factory()->type(QuestionType::FileUpload)->create(['exam_id' => $exam->id]);
    $attempt = Attempt::factory()->verified()->create(['exam_id' => $exam->id]);

    $file = UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf');

    $response = $this->post(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/questions/{$question->id}/answer",
        ['response' => $file],
        ['X-Attempt-Token' => $attempt->token]
    );

    $response->assertOk();

    $answer = $attempt->answers()->first();

    Storage::disk('local')->assertExists($answer->response['path']);
});

it('can complete an attempt after answering all required questions', function () {
    $exam = Exam::factory()->create();
    $question = Question::factory()->type(QuestionType::Text)->create([
        'exam_id' => $exam->id,
        'is_required' => true,
    ]);
    $attempt = Attempt::factory()->verified()->create(['exam_id' => $exam->id]);

    $this->postJson(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/questions/{$question->id}/answer",
        ['response' => 'my answer'],
        ['X-Attempt-Token' => $attempt->token]
    );

    $response = $this->postJson(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/complete",
        [],
        ['X-Attempt-Token' => $attempt->token]
    );

    $response->assertOk()->assertJson(['status' => true]);

    $this->assertDatabaseHas('attempts', [
        'id' => $attempt->id,
    ]);

    expect($attempt->fresh()->completed_at)->not->toBeNull();
});

it('cannot complete an attempt when required questions are missing', function () {
    $exam = Exam::factory()->create();
    Question::factory()->type(QuestionType::Text)->create([
        'exam_id' => $exam->id,
        'is_required' => true,
    ]);
    $attempt = Attempt::factory()->verified()->create(['exam_id' => $exam->id]);

    $response = $this->postJson(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/complete",
        [],
        ['X-Attempt-Token' => $attempt->token]
    );

    $response->assertUnprocessable()->assertJsonValidationErrors('questions');
});

it('cannot complete an already completed attempt', function () {
    $exam = Exam::factory()->create();
    $attempt = Attempt::factory()->verified()->completed()->create(['exam_id' => $exam->id]);

    $response = $this->postJson(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/complete",
        [],
        ['X-Attempt-Token' => $attempt->token]
    );

    $response->assertForbidden();
});
