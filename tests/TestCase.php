<?php

namespace Tests;

use App\AI\Contracts\AIProviderInterface;
use App\Models\Site;
use App\Services\Security\HmacSigner;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\FakeAIProvider;

abstract class TestCase extends BaseTestCase
{
    protected function siteToken(): string
    {
        return 'cp_live_testtoken1234567890abcdef';
    }

    protected function siteSecret(): string
    {
        return 'site-secret-value-for-tests';
    }

    protected function authenticatedSite(array $attributes = []): Site
    {
        return Site::factory()
            ->withSettings()
            ->withToken($this->siteToken())
            ->withSecret($this->siteSecret())
            ->create($attributes);
    }

    /**
     * @param  array<string, string>  $extra
     * @return array<string, string>
     */
    protected function siteHeaders(?string $token = null, array $extra = []): array
    {
        return [
            'Authorization' => 'Bearer '.($token ?? $this->siteToken()),
            'Accept' => 'application/json',
            ...$extra,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function signedHeaders(string $body = '', ?string $secret = null, ?int $timestamp = null, string $method = 'POST', string $path = '/api/v1/events'): array
    {
        $timestamp ??= now()->timestamp;
        $signature = app(HmacSigner::class)->signRequest(
            (string) $timestamp,
            $method,
            $path,
            $body,
            $secret ?? $this->siteSecret(),
        );

        return [
            'X-CommercePilot-Timestamp' => (string) $timestamp,
            'X-CommercePilot-Signature' => $signature,
        ];
    }

    protected function fakeAI(): FakeAIProvider
    {
        $provider = new FakeAIProvider;
        $this->app->instance(AIProviderInterface::class, $provider);

        return $provider;
    }
}
