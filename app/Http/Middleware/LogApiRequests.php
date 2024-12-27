<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogApiRequests
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'data' => $request->all()
        ]);

        $response = $next($request);

        \Log::info('API Response', [
            'status' => $response->status(),
            'content' => $response->content()
        ]);

        return $response;
    }
}