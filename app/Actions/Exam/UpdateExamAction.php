<?php

namespace App\Actions\Exam;

use App\Enums\ExamStatus;
use App\Models\Exam;

class UpdateExamAction
{
    public function execute(
        Exam $exam,
        array $data
    ): Exam {
        if (($data['status'] ?? null) === ExamStatus::Published->value) {
            if ($exam->status !== ExamStatus::Published) {
                $data['published_at'] ??= now();
            }
        }

        $exam->update($data);

        return $exam->refresh();
    }
}
