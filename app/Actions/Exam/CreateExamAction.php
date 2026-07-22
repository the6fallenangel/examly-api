<?php

namespace App\Actions\Exam;

use App\Enums\ExamStatus;
use App\Models\Exam;
use App\Models\User;

class CreateExamAction
{
    public function execute(
        User $user,
        array $data
    ): Exam {
        $status = $data['status'] ?? 'draft';
        $publishedAt = $data['published_at'] ?? null;

        return $user->exams()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'slug' => $data['slug'],
            'status' => $status,
            'published_at' => $publishedAt ?? ($status === ExamStatus::Draft->value ? null : now()),
        ]);
    }
}
