<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SiteSignatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_signature_is_accepted(): void
    {
        $this->authenticatedSite();
        $payload = ['type' => 'tokens'];
        $body = (string) json_encode($payload);

        $this->postJson('/api/v1/events', $payload, $this->siteHeaders(extra: $this->signedHeaders($body)))
            ->assertAccepted();
    }

    public function test_invalid_signature_returns_401(): void
    {
        $this->authenticatedSite();

        $this->postJson('/api/v1/events', ['type' => 'tokens'], $this->siteHeaders(extra: [
            'X-CommercePilot-Timestamp' => (string) now()->timestamp,
            'X-CommercePilot-Signature' => 'invalid',
        ]))
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'invalid_signature');
    }

    public function test_missing_signature_returns_401(): void
    {
        $this->authenticatedSite();

        $this->postJson('/api/v1/events', ['type' => 'tokens'], $this->siteHeaders())
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'missing_signature');
    }

    public function test_expired_timestamp_returns_401(): void
    {
        $this->authenticatedSite();
        $payload = ['type' => 'tokens'];
        $body = (string) json_encode($payload);

        $this->postJson(
            '/api/v1/events',
            $payload,
            $this->siteHeaders(extra: $this->signedHeaders($body, timestamp: now()->subMinutes(10)->timestamp)),
        )
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'expired_signature');
    }

    public function test_replayed_signature_returns_401(): void
    {
        $this->authenticatedSite();
        $payload = ['type' => 'tokens'];
        $body = (string) json_encode($payload);
        $headers = $this->siteHeaders(extra: $this->signedHeaders($body));

        $this->postJson('/api/v1/events', $payload, $headers)->assertAccepted();
        $this->postJson('/api/v1/events', $payload, $headers)
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'replayed_request');
    }

    public function test_modified_request_body_returns_401(): void
    {
        $this->authenticatedSite();
        $body = (string) json_encode(['type' => 'tokens']);

        $this->postJson(
            '/api/v1/events',
            ['type' => 'message'],
            $this->siteHeaders(extra: $this->signedHeaders($body)),
        )
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'invalid_signature');
    }
}
