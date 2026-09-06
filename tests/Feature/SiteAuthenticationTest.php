<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Services\Security\SiteTokenService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SiteAuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_token_returns_the_authenticated_site(): void
    {
        $this->authenticatedSite(['name' => 'Fashion Store']);

        $this->getJson('/api/v1/site', $this->siteHeaders())
            ->assertOk()
            ->assertJsonPath('data.name', 'Fashion Store');
    }

    public function test_invalid_token_returns_401(): void
    {
        $this->authenticatedSite();

        $this->getJson('/api/v1/site', $this->siteHeaders('cp_live_invalid'))
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'invalid_site_token');
    }

    public function test_missing_token_returns_401(): void
    {
        $this->getJson('/api/v1/site')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'missing_site_token');
    }

    public function test_revoked_token_returns_403(): void
    {
        $site = $this->authenticatedSite();
        app(SiteTokenService::class)->revoke($site);

        $this->getJson('/api/v1/site', $this->siteHeaders())
            ->assertForbidden()
            ->assertJsonPath('error.code', 'site_inactive');
    }

    public function test_inactive_site_returns_403(): void
    {
        $this->authenticatedSite(['status' => SiteStatus::Inactive]);

        $this->getJson('/api/v1/site', $this->siteHeaders())
            ->assertForbidden()
            ->assertJsonPath('error.code', 'site_inactive');
    }

    public function test_token_rotation_issues_a_new_token_and_invalidates_the_old_one(): void
    {
        $this->authenticatedSite();
        $body = '[]';

        $response = $this->postJson(
            '/api/v1/site/token/rotate',
            [],
            $this->siteHeaders(extra: $this->signedHeaders($body, method: 'POST', path: '/api/v1/site/token/rotate')),
        );

        $response->assertOk();
        $newToken = $response->json('token');
        $this->assertIsString($newToken);
        $this->assertNotSame($this->siteToken(), $newToken);

        $this->getJson('/api/v1/site', $this->siteHeaders())
            ->assertUnauthorized();

        $this->getJson('/api/v1/site', $this->siteHeaders($newToken))
            ->assertOk();
    }

    public function test_raw_token_is_never_stored(): void
    {
        $site = $this->authenticatedSite();

        $this->assertDatabaseMissing('sites', [
            'id' => $site->id,
            'site_token_hash' => $this->siteToken(),
        ]);
        $this->assertSame(hash('sha256', $this->siteToken()), $site->site_token_hash);
    }
}
