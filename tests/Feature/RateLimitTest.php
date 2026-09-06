<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_site_rate_limit_returns_429(): void
    {
        Config::set('commercepilot.rate_limits.site_per_minute', 1);
        Config::set('commercepilot.rate_limits.visitor_per_minute', 100);
        Config::set('commercepilot.rate_limits.site_per_day', 1000);
        $this->authenticatedSite();

        $this->getJson('/api/v1/site', $this->siteHeaders())->assertOk();
        $this->getJson('/api/v1/site', $this->siteHeaders())
            ->assertStatus(429)
            ->assertJsonPath('error.code', 'rate_limit_exceeded');
    }

    public function test_visitor_rate_limit_returns_429(): void
    {
        Config::set('commercepilot.rate_limits.visitor_per_minute', 1);
        Config::set('commercepilot.rate_limits.site_per_minute', 100);
        Config::set('commercepilot.rate_limits.site_per_day', 1000);
        $this->authenticatedSite();
        $this->fakeAI();
        $payload = [
            'visitor_id' => '11111111-1111-1111-1111-111111111111',
            'message' => 'Hello',
        ];

        $this->postJson('/api/v1/chat', $payload, $this->siteHeaders())->assertOk();
        $this->postJson('/api/v1/chat', $payload, $this->siteHeaders())
            ->assertStatus(429)
            ->assertJsonPath('error.code', 'rate_limit_exceeded');
    }
}
