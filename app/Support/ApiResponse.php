<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'status' => true,
            'message' => $message,
            ...($data != null ? ['data' => $data] : []),
        ], $statusCode);
    }

    public static function error(
        string $message,
        int $statusCode = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
