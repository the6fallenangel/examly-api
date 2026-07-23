<?php

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can resume an attempt with previously submitted answers', function () {
    $exam = Exam::factory()->create();
    $question = Question::factory()->type(QuestionType::Text)->create(['exam_id' => $exam->id]);
    $attempt = Attempt::factory()->verified()->create(['exam_id' => $exam->id]);

    Answer::factory()->create([
        'attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'response' => 'previous answer',
    ]);

    $response = $this->getJson(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/resume",
        ['X-Attempt-Token' => $attempt->token]
    );

    $response
        ->assertOk()
        ->assertJson([
            'status' => true,
            'data' => [
                'attempt' => ['id' => $attempt->id],
            ],
        ]);

    expect($response->json('data.answers.0.question_id'))->toBe($question->id);
});

it('cannot resume with an invalid attempt token', function () {
    $exam = Exam::factory()->create();
    $attempt = Attempt::factory()->verified()->create(['exam_id' => $exam->id]);

    $response = $this->getJson(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/resume",
        ['X-Attempt-Token' => 'wrong-token']
    );

    $response->assertForbidden();
});

it('cannot resume an unverified attempt', function () {
    $exam = Exam::factory()->create();
    $attempt = Attempt::factory()->create(['exam_id' => $exam->id]);

    $response = $this->getJson(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/resume",
        ['X-Attempt-Token' => $attempt->token]
    );

    $response->assertForbidden();
});

it('can still resume a completed attempt to view final answers', function () {
    $exam = Exam::factory()->create();
    $attempt = Attempt::factory()->verified()->completed()->create(['exam_id' => $exam->id]);

    $response = $this->getJson(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/resume",
        ['X-Attempt-Token' => $attempt->token]
    );

    $response->assertOk()->assertJson(['status' => true]);
});

it('cannot submit a new answer to an already completed attempt', function () {
    $exam = Exam::factory()->create();
    $question = Question::factory()->type(QuestionType::Text)->create(['exam_id' => $exam->id]);
    $attempt = Attempt::factory()->verified()->completed()->create(['exam_id' => $exam->id]);

    $response = $this->postJson(
        "/api/v1/public/exams/{$exam->slug}/attempts/{$attempt->id}/questions/{$question->id}/answer",
        ['response' => 'too late'],
        ['X-Attempt-Token' => $attempt->token]
    );

    $response->assertForbidden();
});
