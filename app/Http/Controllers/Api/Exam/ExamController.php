<?php

namespace App\Http\Controllers\Api\Exam;

use App\Actions\Exam\CreateExamAction;
use App\Actions\Exam\UpdateExamAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\StoreExamRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Http\Resources\ExamResource;
use App\Models\Exam;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ExamController extends Controller
{
    public function index(): JsonResponse
    {
        $exams = request()->user()->exams()
            ->withCount(['questions', 'attempts'])->latest()->paginate(15);

        return ApiResponse::paginated($exams, ExamResource::class);
    }

    public function store(
        StoreExamRequest $req,
        CreateExamAction $act
    ): JsonResponse {
        $exam = $act->execute(
            user: request()->user(),
            data: $req->validated()
        )->loadCount(['questions', 'attempts']);

        return ApiResponse::success(
            message: 'exam created successfully',
            data: new ExamResource($exam),
            statusCode: Response::HTTP_CREATED
        );
    }

    public function show(Exam $exam)
    {
        $this->authorize('view', $exam);

        return ApiResponse::success(
            data: new ExamResource($exam->loadCount(['questions', 'attempts']))
        );
    }

    public function update(
        UpdateExamRequest $req,
        Exam $exam,
        UpdateExamAction $act
    ): JsonResponse {
        $this->authorize('update', $exam);

        $exam = $act->execute(
            exam: $exam,
            data: $req->validated()
        );

        $exam->loadCount(['attempts', 'questions']);

        return ApiResponse::success(
            data: new ExamResource($exam),
            message: 'exam updated successfully'
        );
    }

    public function destroy(Exam $exam)
    {
        $this->authorize('delete', $exam);

        $exam->delete();

        return ApiResponse::success(
            message: 'exam deleted successfully'
        );
    }
}
