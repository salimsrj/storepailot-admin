<?php

namespace App\Http\Middleware;

use App\Enums\SiteStatus;
use App\Exceptions\SiteAuthenticationException;
use App\Exceptions\SiteSignatureException;
use App\Models\Site;
use App\Services\Security\HmacSigner;
use App\Services\Security\SiteSecretService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class VerifySiteSignature
{
    public function __construct(
        private HmacSigner $signer,
        private SiteSecretService $secrets,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $site = $request->attributes->get('site');

        if (! $site instanceof Site) {
            throw SiteAuthenticationException::invalidToken();
        }

        if ($site->status !== SiteStatus::Active) {
            throw SiteAuthenticationException::inactiveSite();
        }

        $timestamp = $request->header('X-CommercePilot-Timestamp');
        $signature = $request->header('X-CommercePilot-Signature');

        if (! is_string($timestamp) || $timestamp === '' || ! is_string($signature) || $signature === '') {
            throw SiteSignatureException::missing();
        }

        if (! ctype_digit($timestamp)) {
            throw SiteSignatureException::invalid();
        }

        $tolerance = (int) config('commercepilot.hmac.timestamp_tolerance_seconds');

        if (abs(now()->timestamp - (int) $timestamp) > $tolerance) {
            throw SiteSignatureException::expired();
        }

        $replayKey = 'hmac:replay:'.$site->id.':'.$signature;

        if (! Cache::add($replayKey, true, $tolerance)) {
            throw SiteSignatureException::replayed();
        }

        $valid = $this->signer->verifyRequest(
            $timestamp,
            $request->getMethod(),
            $this->signer->canonicalizePath($this->rawPath($request)),
            $request->getContent(),
            $this->secrets->secretFor($site),
            $signature,
        );

        if (! $valid) {
            Cache::forget($replayKey);

            throw SiteSignatureException::invalid();
        }

        return $next($request);
    }

    /**
     * Path as the caller knows it: excludes any subdirectory the app is mounted
     * under, includes the query string.
     */
    private function rawPath(Request $request): string
    {
        $query = $request->getQueryString();

        return $request->getPathInfo().($query !== null && $query !== '' ? '?'.$query : '');
    }
}
