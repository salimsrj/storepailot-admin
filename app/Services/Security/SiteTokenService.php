<?php

namespace App\Services\Security;

use App\Enums\SiteStatus;
use App\Models\Site;
use Illuminate\Support\Str;

class SiteTokenService
{
    public function generate(): string
    {
        return config('commercepilot.site_tokens.prefix').Str::lower(Str::random(
            (int) config('commercepilot.site_tokens.length'),
        ));
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public function findActiveSite(string $token): ?Site
    {
        if (! $this->isValidFormat($token)) {
            return null;
        }

        return Site::query()
            ->where('site_token_hash', $this->hash($token))
            ->first();
    }

    public function assign(Site $site): string
    {
        $token = $this->generate();

        $site->forceFill([
            'site_token_hash' => $this->hash($token),
            'status' => SiteStatus::Active,
        ])->save();

        return $token;
    }

    public function rotate(Site $site): string
    {
        return $this->assign($site);
    }

    public function revoke(Site $site): void
    {
        $site->forceFill([
            'status' => SiteStatus::Revoked,
        ])->save();
    }

    public function isValidFormat(string $token): bool
    {
        $prefix = (string) config('commercepilot.site_tokens.prefix');

        return str_starts_with($token, $prefix) && strlen($token) > strlen($prefix);
    }
}
