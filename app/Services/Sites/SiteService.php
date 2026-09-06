<?php

namespace App\Services\Sites;

use App\Enums\SiteStatus;
use App\Events\SiteConnected;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Security\SiteSecretService;
use App\Services\Security\SiteTokenService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Uri;

class SiteService
{
    public function __construct(
        private SiteTokenService $tokens,
        private SiteSecretService $secrets,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{site: Site, token: string, secret: string}
     */
    public function register(User $user, array $attributes): array
    {
        return DB::transaction(function () use ($user, $attributes): array {
            $url = (string) $attributes['url'];
            $host = Uri::of($url)->host() ?? parse_url($url, PHP_URL_HOST);
            $token = $this->tokens->generate();
            $secret = $this->secrets->generate();

            $site = new Site([
                'user_id' => $user->id,
                'name' => $attributes['name'],
                'url' => $url,
                'domain' => $host ?: $url,
                'plugin_version' => $attributes['plugin_version'] ?? null,
                'wordpress_version' => $attributes['wordpress_version'] ?? null,
                'woocommerce_version' => $attributes['woocommerce_version'] ?? null,
                'status' => SiteStatus::Active,
            ]);

            $site->forceFill([
                'site_token_hash' => $this->tokens->hash($token),
                'site_secret_encrypted' => $this->secrets->encrypt($secret),
            ])->save();

            SiteSetting::query()->create([
                'site_id' => $site->id,
            ]);

            event(new SiteConnected($site));

            return [
                'site' => $site->refresh(),
                'token' => $token,
                'secret' => $secret,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Site $site, array $attributes): Site
    {
        if (isset($attributes['url'])) {
            $host = Uri::of((string) $attributes['url'])->host();
            $attributes['domain'] = $host ?: $site->domain;
        }

        $site->fill($attributes)->save();
        Cache::forget('site-settings:'.$site->id);

        return $site->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateSettings(Site $site, array $attributes): SiteSetting
    {
        $settings = $site->settings()->firstOrCreate([]);
        $settings->fill($attributes)->save();
        Cache::forget('site-settings:'.$site->id);

        return $settings->refresh();
    }

    /**
     * @return array{token: string}
     */
    public function rotateToken(Site $site): array
    {
        return ['token' => $this->tokens->rotate($site)];
    }
}
