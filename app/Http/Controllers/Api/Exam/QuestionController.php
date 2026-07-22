<?php

namespace App\Http\Controllers\Api\Exam;

use App\Actions\Question\CreateQuestionAction;
use App\Actions\Question\UpdateQuestionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Question\StoreQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Exam;
use App\Models\Question;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class QuestionController extends Controller
{
    public function index(Exam $exam): JsonResponse
    {
        $this->authorize('view', $exam);

        return ApiResponse::success(
            data: QuestionResource::collection($exam->questions)
        );
    }

    public function store(
        StoreQuestionRequest $req,
        Exam $exam,
        CreateQuestionAction $act
    ): JsonResponse {
        $this->authorize('update', $exam);

        $question = $act->execute($exam, $req->validated());

        return ApiResponse::success(
            message: 'question created successfully',
            data: new QuestionResource($question),
            statusCode: Response::HTTP_CREATED
        );
    }

    public function show(Exam $exam, Question $question): JsonResponse
    {
        $this->authorize('view', $exam);

        return ApiResponse::success(
            data: new QuestionResource($question)
        );
    }

    public function update(
        UpdateQuestionRequest $req,
        Exam $exam,
        Question $question,
        UpdateQuestionAction $act
    ): JsonResponse {
        $this->authorize('update', $exam);

        $question = $act->execute($question, $req->validated());

        return ApiResponse::success(
            data: new QuestionResource($question),
            message: 'question updated successfully'
        );
    }

    public function destroy(Exam $exam, Question $question): JsonResponse
    {
        $this->authorize('update', $exam);

        $question->delete();

        return ApiResponse::success(
            message: 'question deleted successfully'
        );
    }
}
