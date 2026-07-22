<?php

namespace App\Http\Controllers\Api\Public;

use App\Actions\Exam\ResolvePublicExamAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\PublicExamResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PublicExamController extends Controller
{
    public function show(string $slug, ResolvePublicExamAction $act): JsonResponse
    {
        $exam = $act->execute($slug);

        return ApiResponse::success(
            data: new PublicExamResource($exam->load('questions'))
        );
    }
}
