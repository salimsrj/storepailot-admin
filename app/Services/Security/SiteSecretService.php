<?php

namespace App\Services\Security;

use App\Models\Site;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class SiteSecretService
{
    public function generate(): string
    {
        return Str::random(64);
    }

    public function encrypt(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    public function decrypt(string $encrypted): string
    {
        return Crypt::decryptString($encrypted);
    }

    public function secretFor(Site $site): string
    {
        return $this->decrypt($site->site_secret_encrypted);
    }

    public function assign(Site $site): string
    {
        $secret = $this->generate();

        $site->forceFill([
            'site_secret_encrypted' => $this->encrypt($secret),
        ])->save();

        return $secret;
    }

    public function rotate(Site $site): string
    {
        return $this->assign($site);
    }
}
