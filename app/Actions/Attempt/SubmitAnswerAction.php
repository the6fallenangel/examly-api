<?php

namespace App\Actions\Attempt;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Question;
use Illuminate\Http\UploadedFile;

class SubmitAnswerAction
{
    public function execute(Attempt $attempt, Question $question, array $validated): Answer
    {
        $response = $question->type === QuestionType::FileUpload
            ? $this->storeFile($attempt, $question, $validated['response'])
            : $validated['response'];

        return Answer::query()->updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $question->id],
            ['response' => $response]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function storeFile(Attempt $attempt, Question $question, UploadedFile $file): string
    {
        return $file->store("attempts/{$attempt->id}/{$question->id}", 'local');
    }
}
