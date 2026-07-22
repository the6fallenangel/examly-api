<?php

namespace App\Http\Resources;

use App\Enums\QuestionType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttemptAnswerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'question_prompt' => $this->whenLoaded('question', fn () => $this->question->prompt),
            'question_type' => $this->whenLoaded('question', fn () => $this->question->type),
            'response' => $this->response,
            'download_url' => $this->when(
                $this->relationLoaded('question') && $this->question->type === QuestionType::FileUpload,
                fn () => route('exams.attempts.answers.download', [
                    'exam' => $this->question->exam_id,
                    'attempt' => $this->attempt_id,
                    'answer' => $this->id,
                ])
            ),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
