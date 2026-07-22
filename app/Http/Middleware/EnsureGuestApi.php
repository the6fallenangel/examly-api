<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuestApi
{
    public static function alreadyAuthenticatedResponse(): JsonResponse
    {
        return ApiResponse::error(
            message: 'you are already authenticated',
            statusCode: 403
        );
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            return static::alreadyAuthenticatedResponse();
        }

        return $next($request);
    }
}
