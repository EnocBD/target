<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RateLimitForms
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '5', string $decayMinutes = '1'): Response
    {
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts);
        }

        RateLimiter::increment($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            RateLimiter::retriesLeft($key, $maxAttempts)
        );
    }

    /**
     * Resolve request signature.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $userId = $request->user()?->id;

        return sha1(
            $request->method() .
            '|' . $request->server('SERVER_NAME') .
            '|' . ($userId ?: $request->ip()) .
            '|' . $request->path()
        );
    }

    /**
     * Create a 'too many attempts' response.
     */
    protected function buildResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Demasiados intentos. Intenta nuevamente en ' . $retryAfter . ' segundos.',
                'retry_after' => $retryAfter
            ], 429);
        }

        return back()->withErrors([
            'rate_limit' => 'Demasiados intentos. Intenta nuevamente en ' . $retryAfter . ' segundos.'
        ])->withInput();
    }

    /**
     * Add the limit header information to the given response.
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);

        return $response;
    }
}