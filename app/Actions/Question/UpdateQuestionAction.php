<?php

namespace App\Actions\Question;

use App\Models\Question;

class UpdateQuestionAction
{
    public function execute(Question $question, array $data): Question
    {
        $question->update($data);

        return $question->refresh();
    }
}
