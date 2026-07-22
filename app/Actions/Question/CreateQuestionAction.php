<?php

namespace App\Actions\Question;

use App\Models\Exam;
use App\Models\Question;

class CreateQuestionAction
{
    public function execute(Exam $exam, array $data): Question
    {
        $data['sort_order'] ??= $exam->questions()->max('sort_order') + 1;

        return $exam->questions()->create($data);
    }
}
