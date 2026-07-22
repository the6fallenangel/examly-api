<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        return response()->json([
            'status' => true,
            'message' => $message,
            ...($data !== null ? ['data' => $data] : []),
        ], $statusCode);
    }

    public static function paginated(LengthAwarePaginator $paginator, ?string $resource = null, string $message = 'Success'): JsonResponse
    {
        $items = $paginator->items();
        $items = $resource ? $resource::collection($items) : $items;

        return self::success(data: [
            'items' => $items,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ], message: $message);
    }

    public static function error(
        string $message,
        int $statusCode = Response::HTTP_BAD_REQUEST,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'status' => false,
            'message' => $message,
            ...($errors !== null ? ['errors' => $errors] : []),
        ], $statusCode);
    }
}
