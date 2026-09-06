<?php

namespace App\Http\Middleware;

use App\Enums\SiteStatus;
use App\Exceptions\SiteAuthenticationException;
use App\Services\Security\SiteTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSite
{
    public function __construct(private SiteTokenService $tokens) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! is_string($token) || $token === '') {
            throw SiteAuthenticationException::missingToken();
        }

        $site = $this->tokens->findActiveSite($token);

        if ($site === null) {
            throw SiteAuthenticationException::invalidToken();
        }

        if ($site->status !== SiteStatus::Active) {
            throw SiteAuthenticationException::inactiveSite();
        }

        $request->attributes->set('site', $site);
        Context::add('site_id', $site->id);

        $site->forceFill(['last_seen_at' => now()])->save();

        return $next($request);
    }
}
