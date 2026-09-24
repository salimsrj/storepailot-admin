<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Services\Security\SiteSecretService;
use App\Services\Security\SiteTokenService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CredentialsController extends Controller
{
    public function __invoke(
        Request $request,
        SiteTokenService $tokens,
        SiteSecretService $secrets,
    ): View {
        $user = $request->user();
        $site = $user->sites()->latest('id')->first();
        $verified = $user->hasVerifiedEmail();

        $credentials = null;

        if ($verified && $site) {
            $credentials = [
                'api_url' => rtrim((string) config('app.url'), '/').'/api/v1',
                'site_id' => $site->uuid,
                'site_token' => $tokens->tokenFor($site),
                'site_secret' => $secrets->secretFor($site),
            ];
        }

        return view('merchant.credentials.show', [
            'user' => $user,
            'site' => $site,
            'verified' => $verified,
            'credentials' => $credentials,
        ]);
    }
}
