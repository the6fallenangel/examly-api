<?php

namespace App\Http\Controllers\Api\Exam;

use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttemptResource;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Exam;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttemptController extends Controller
{
    public function index(Exam $exam): JsonResponse
    {
        $this->authorize('view', $exam);

        $attempts = $exam->attempts()
            ->verified()
            ->withCount('answers')
            ->latest('verified_at')
            ->paginate(15);

        return ApiResponse::paginated($attempts, AttemptResource::class);
    }

    public function show(Exam $exam, Attempt $attempt): JsonResponse
    {
        $this->authorize('view', $exam);

        $attempt->load('answers.question');

        return ApiResponse::success(
            data: new AttemptResource($attempt)
        );
    }

    public function downloadAnswerFile(Exam $exam, Attempt $attempt, Answer $answer): StreamedResponse
    {
        $this->authorize('view', $exam);

        abort_unless($answer->question->type === QuestionType::FileUpload, 404);

        abort_unless(Storage::disk('local')->exists($answer->response), 404);

        return Storage::disk('local')->download($answer->response);
    }
}
