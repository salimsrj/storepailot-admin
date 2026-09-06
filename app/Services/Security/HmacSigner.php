<?php

namespace App\Services\Security;

class HmacSigner
{
    public function sign(string $timestamp, string $body, string $secret): string
    {
        return hash_hmac('sha256', $timestamp.'.'.$body, $secret);
    }

    public function verify(string $timestamp, string $body, string $secret, string $signature): bool
    {
        return hash_equals($this->sign($timestamp, $body, $secret), $signature);
    }

    /**
     * Binds a signature to the specific request it was minted for.
     *
     * Without the method and path in the signed payload every GET (which has an
     * empty body) produces the same digest for a given second, so concurrent
     * polling requests collide in replay protection and a captured signature
     * could be reused against a different endpoint.
     */
    public function signRequest(string $timestamp, string $method, string $path, string $body, string $secret): string
    {
        return $this->sign($timestamp, strtoupper($method).'.'.$this->canonicalizePath($path).'.'.$body, $secret);
    }

    /**
     * Sort query parameters so WordPress and Laravel sign the same string
     * even when HTTP clients reorder ?a=&b=.
     */
    public function canonicalizePath(string $path): string
    {
        $parts = explode('?', $path, 2);
        $base = $parts[0];

        if (! isset($parts[1]) || $parts[1] === '') {
            return $base;
        }

        parse_str($parts[1], $params);

        if ($params === []) {
            return $base;
        }

        ksort($params);

        return $base.'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public function verifyRequest(string $timestamp, string $method, string $path, string $body, string $secret, string $signature): bool
    {
        return hash_equals($this->signRequest($timestamp, $method, $path, $body, $secret), $signature);
    }
}
