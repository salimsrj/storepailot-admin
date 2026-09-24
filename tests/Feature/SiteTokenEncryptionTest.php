<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Security\SiteTokenService;
use App\Services\Sites\SiteService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SiteTokenEncryptionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_registering_a_site_stores_an_encrypted_token(): void
    {
        $user = User::factory()->create();

        $result = app(SiteService::class)->register($user, [
            'name' => 'Demo Shop',
            'url' => 'https://shop.example.com',
        ]);

        $site = $result['site']->fresh();

        $this->assertNotEmpty($site->site_token_encrypted);
        $this->assertSame($result['token'], app(SiteTokenService::class)->tokenFor($site));
        $this->assertSame(hash('sha256', $result['token']), $site->site_token_hash);
    }

    public function test_rotating_a_token_updates_the_encrypted_value(): void
    {
        $user = User::factory()->create();
        $result = app(SiteService::class)->register($user, [
            'name' => 'Demo Shop',
            'url' => 'https://shop.example.com',
        ]);

        $rotated = app(SiteService::class)->rotateToken($result['site']);
        $site = $result['site']->fresh();

        $this->assertNotSame($result['token'], $rotated['token']);
        $this->assertSame($rotated['token'], app(SiteTokenService::class)->tokenFor($site));
    }
}
