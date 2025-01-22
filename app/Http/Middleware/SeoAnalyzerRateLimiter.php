<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class SeoAnalyzerRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'seo-analyzer:' . ($request->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($key, 10)) { // 10 istek limiti
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => "Çok fazla istek gönderdiniz. Lütfen {$seconds} saniye bekleyin.",
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, 300); // 5 dakika süreyle

        return $next($request);
    }
}
