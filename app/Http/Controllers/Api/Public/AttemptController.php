<?php

namespace App\Http\Controllers\Api\Public;

use App\Actions\Attempt\RequestAttemptOtpAction;
use App\Actions\Attempt\VerifyAttemptOtpAction;
use App\Actions\Exam\ResolvePublicExamAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attempt\RequestAttemptOtpRequest;
use App\Http\Requests\Attempt\VerifyAttemptOtpRequest;
use App\Http\Resources\PublicExamResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

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
                'exam' => new PublicExamResource($exam->load('questions')),
            ]
        );
    }
}
