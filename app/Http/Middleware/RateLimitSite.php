<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitSite
{
    public function __construct(private RateLimiter $limiter) {}

    /**
     * @param  string  $profile  'chat' for AI-backed traffic, 'poll' for cheap
     *                           conversation reads that must not consume the
     *                           daily chat allowance.
     */
    public function handle(Request $request, Closure $next, string $profile = 'chat'): Response
    {
        $site = $request->attributes->get('site');

        if (! $site instanceof Site) {
            return $next($request);
        }

        $limits = $profile === 'poll'
            ? [['poll-site:'.$site->id, (int) config('commercepilot.rate_limits.poll_site_per_minute'), 60]]
            : [
                [$this->visitorKey($request, $site), (int) config('commercepilot.rate_limits.visitor_per_minute'), 60],
                ['site:'.$site->id, (int) config('commercepilot.rate_limits.site_per_minute'), 60],
                ['site-day:'.$site->id.':'.now()->toDateString(), (int) config('commercepilot.rate_limits.site_per_day'), 86400],
            ];

        foreach ($limits as [$key, $maxAttempts, $decaySeconds]) {
            if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
                return $this->tooManyAttemptsResponse($this->limiter->availableIn($key));
            }
        }

        foreach ($limits as [$key, $maxAttempts, $decaySeconds]) {
            $this->limiter->hit($key, $decaySeconds);
        }

        return $next($request);
    }

    private function visitorKey(Request $request, Site $site): string
    {
        $visitor = $request->input('visitor_id');

        if (! is_string($visitor) || $visitor === '') {
            $visitor = $request->ip() ?? 'unknown';
        }

        return 'visitor:'.$site->id.':'.$visitor;
    }

    private function tooManyAttemptsResponse(int $retryAfter): Response
    {
        return response()->json([
            'error' => [
                'code' => 'rate_limit_exceeded',
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter,
            ],
        ], 429)->withHeaders([
            'Retry-After' => (string) $retryAfter,
        ]);
    }
}
