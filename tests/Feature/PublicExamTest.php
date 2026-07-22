<?php

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can view a published exam by slug', function () {
    $exam = Exam::factory()->create();

    Question::factory()->count(2)->create(['exam_id' => $exam->id]);

    $response = $this->getJson("/api/v1/public/exams/{$exam->slug}");

    $response->assertOk()->assertJson(['status' => true]);
});

it('returns 404 for a draft exam', function () {
    $exam = Exam::factory()->draft()->create();

    $response = $this->getJson("/api/v1/public/exams/{$exam->slug}");

    $response->assertNotFound();
});

it('returns 404 for an unknown slug', function () {
    $response = $this->getJson('/api/v1/public/exams/does-not-exist');

    $response->assertNotFound();
});
