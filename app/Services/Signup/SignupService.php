<?php

namespace App\Services\Signup;

use App\Enums\UserStatus;
use App\Models\Site;
use App\Models\User;
use App\Services\Sites\SiteService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Uri;

class SignupService
{
    public function __construct(private SiteService $sites) {}

    /**
     * @param  array{website_url: string, first_name: string, last_name: string, email: string, password: string}  $attributes
     * @return array{user: User, site: Site}
     */
    public function register(array $attributes): array
    {
        $result = DB::transaction(function () use ($attributes): array {
            $firstName = trim($attributes['first_name']);
            $lastName = trim($attributes['last_name']);
            $websiteUrl = (string) $attributes['website_url'];
            $host = Uri::of($websiteUrl)->host() ?? parse_url($websiteUrl, PHP_URL_HOST);

            $user = User::query()->create([
                'name' => trim($firstName.' '.$lastName),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $attributes['email'],
                'password' => $attributes['password'],
                'status' => UserStatus::Active,
                'is_admin' => false,
            ]);

            $siteResult = $this->sites->register($user, [
                'name' => $host ?: $websiteUrl,
                'url' => $websiteUrl,
            ]);

            return [
                'user' => $user->refresh(),
                'site' => $siteResult['site'],
            ];
        });

        $result['user']->sendEmailVerificationNotification();

        return $result;
    }
}
