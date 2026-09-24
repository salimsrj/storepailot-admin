<?php

namespace Database\Factories;

use App\Enums\SiteStatus;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $domain = fake()->unique()->domainName();

        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'url' => 'https://'.$domain,
            'domain' => $domain,
            'plugin_version' => '1.0.0',
            'wordpress_version' => '6.8.0',
            'woocommerce_version' => '9.8.0',
            'status' => SiteStatus::Active,
            'last_seen_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Site $site): void {
            if (blank($site->site_token_hash) || blank($site->site_token_encrypted)) {
                $token = 'cp_live_'.Str::random(40);
                $site->forceFill([
                    'site_token_hash' => hash('sha256', $token),
                    'site_token_encrypted' => Crypt::encryptString($token),
                ]);
            }

            if (blank($site->site_secret_encrypted)) {
                $site->forceFill([
                    'site_secret_encrypted' => Crypt::encryptString(Str::random(64)),
                ]);
            }
        });
    }

    public function withSettings(): static
    {
        return $this->has(SiteSetting::factory(), 'settings');
    }

    public function withToken(string $token): static
    {
        return $this->afterMaking(function (Site $site) use ($token): void {
            $site->forceFill([
                'site_token_hash' => hash('sha256', $token),
                'site_token_encrypted' => Crypt::encryptString($token),
            ]);
        });
    }

    public function withSecret(string $secret): static
    {
        return $this->afterMaking(function (Site $site) use ($secret): void {
            $site->forceFill([
                'site_secret_encrypted' => Crypt::encryptString($secret),
            ]);
        });
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SiteStatus::Pending,
            'last_seen_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SiteStatus::Inactive,
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SiteStatus::Revoked,
        ]);
    }
}
