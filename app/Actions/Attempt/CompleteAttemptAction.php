<?php

namespace App\Actions\Attempt;

use App\Models\Attempt;
use Illuminate\Validation\ValidationException;

class CompleteAttemptAction
{
    public function execute(Attempt $attempt): Attempt
    {
        $requiredQuestionIds = $attempt->exam->questions()
            ->where('is_required', true)
            ->pluck('id');

        $answeredQuestionIds = $attempt->answers()->pluck('question_id');

        $missing = $requiredQuestionIds->diff($answeredQuestionIds);

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'questions' => 'please answer all required questions before completing the attempt',
            ]);
        }

        $attempt->update(['completed_at' => now()]);

        return $attempt->refresh();
    }
}
