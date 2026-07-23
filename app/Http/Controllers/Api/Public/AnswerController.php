<?php

namespace App\Http\Controllers\Api\Public;

use App\Actions\Attempt\CompleteAttemptAction;
use App\Actions\Attempt\EnsureAttemptAccessAction;
use App\Actions\Attempt\SubmitAnswerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attempt\StoreAnswerRequest;
use App\Http\Resources\AnswerResource;
use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Question;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function store(
        StoreAnswerRequest $req,
        string $slug,
        Attempt $attempt,
        Question $question,
        EnsureAttemptAccessAction $ensureAccess,
        SubmitAnswerAction $act
    ): JsonResponse {
        $exam = Exam::whereSlug($slug)->firstOrFail();

        $ensureAccess->execute($exam, $attempt, $req->header('X-Attempt-Token'));

        $this->ensureNotCompleted($attempt);

        if ($question->exam_id !== $exam->id) {
            abort(404);
        }

        $answer = $act->execute($attempt, $question, $req->validated());

        return ApiResponse::success(
            message: 'answer saved successfully',
            data: new AnswerResource($answer)
        );
    }

    public function complete(
        Request $request,
        string $slug,
        Attempt $attempt,
        EnsureAttemptAccessAction $ensureAccess,
        CompleteAttemptAction $act
    ): JsonResponse {
        $exam = Exam::whereSlug($slug)->firstOrFail();

        $ensureAccess->execute($exam, $attempt, $request->header('X-Attempt-Token'));

        $this->ensureNotCompleted($attempt);

        $act->execute($attempt);

        return ApiResponse::success(message: 'attempt completed successfully');
    }

    private function ensureNotCompleted(Attempt $attempt): void
    {
        if ($attempt->completed_at !== null) {
            throw new AuthorizationException('this attempt has already been completed');
        }
    }
}
