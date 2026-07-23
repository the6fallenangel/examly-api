<?php

namespace App\Http\Controllers\Api\Public;

use App\Actions\Attempt\EnsureAttemptAccessAction;
use App\Actions\Attempt\RequestAttemptOtpAction;
use App\Actions\Attempt\VerifyAttemptOtpAction;
use App\Actions\Exam\ResolvePublicExamAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attempt\RequestAttemptOtpRequest;
use App\Http\Requests\Attempt\VerifyAttemptOtpRequest;
use App\Http\Resources\AnswerResource;
use App\Http\Resources\PublicExamResource;
use App\Models\Attempt;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttemptController extends Controller
{
    public function requestOtp(
        RequestAttemptOtpRequest $req,
        string $slug,
        ResolvePublicExamAction $resolveExam,
        RequestAttemptOtpAction $act
    ): JsonResponse {
        $exam = $resolveExam->execute($slug);

        $act->execute($exam, $req->validated('email'));

        return ApiResponse::success(message: 'verification code sent successfully');
    }

    public function verifyOtp(
        VerifyAttemptOtpRequest $req,
        string $slug,
        ResolvePublicExamAction $resolveExam,
        VerifyAttemptOtpAction $act
    ): JsonResponse {
        $exam = $resolveExam->execute($slug);

        $attempt = $act->execute(
            exam: $exam,
            email: $req->validated('email'),
            otp: $req->validated('otp'),
            name: $req->validated('name'),
            ip: $req->ip(),
        );

        return ApiResponse::success(
            message: 'identity verified successfully',
            data: [
                'attempt_id' => $attempt->id,
                'attempt_token' => $attempt->token,
                'exam' => new PublicExamResource($exam->load('questions')),
            ]
        );
    }

    public function resume(
        Request $request,
        string $slug,
        Attempt $attempt,
        ResolvePublicExamAction $resolveExam,
        EnsureAttemptAccessAction $ensureAccess
    ): JsonResponse {
        $exam = $resolveExam->execute($slug);

        $ensureAccess->execute($exam, $attempt, $request->header('X-Attempt-Token'));

        return ApiResponse::success(
            data: [
                'attempt' => [
                    'id' => $attempt->id,
                    'taker_name' => $attempt->taker_name,
                    'started_at' => $attempt->started_at?->toISOString(),
                    'completed_at' => $attempt->completed_at?->toISOString(),
                ],
                'exam' => new PublicExamResource($exam->load('questions')),
                'answers' => AnswerResource::collection($attempt->answers),
            ]
        );
    }
}
