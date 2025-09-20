<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('get')) {
            return $next($request);
        }

        $key = $request->header('Idempotency-Key');
        if (!$key) {
            abort(400, 'Idempotency-Key required');
        }

        $cacheKey = "idempotency:{$request->user()?->id}:{$key}";
        $cached = Cache::get($cacheKey);

        if ($cached) {
            return response()->json(json_decode($cached, true), 200);
        }

        $response = $next($request);
        if ($response->getStatusCode() < 300) {
            Cache::put($cacheKey, $response->getContent(), now()->addHours(24));
        }

        return $response;
    }
}