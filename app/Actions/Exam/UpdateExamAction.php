<?php

namespace App\Actions\Exam;

use App\Models\Exam;

class UpdateExamAction
{
    public function execute(
        Exam $exam,
        array $data
    ): Exam {
        $exam->update($data);

        return $exam->refresh();
    }
}
