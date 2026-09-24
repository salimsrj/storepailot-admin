<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Site;
use App\Models\User;
use App\Services\Security\SiteSecretService;
use App\Services\Security\SiteTokenService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MerchantDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_are_redirected_to_merchant_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_merchant_can_update_profile(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
        ]);

        $this->actingAs($user)->put(route('dashboard.profile.update'), [
            'first_name' => 'Augusta',
            'last_name' => 'Byron',
            'email' => 'ada@example.com',
        ])->assertRedirect(route('dashboard.profile.edit'));

        $user->refresh();
        $this->assertSame('Augusta', $user->first_name);
        $this->assertSame('Byron', $user->last_name);
        $this->assertSame('Augusta Byron', $user->name);
    }

    public function test_changing_email_clears_verification(): void
    {
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->put(route('dashboard.profile.update'), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => 'new@example.com',
        ])->assertRedirect(route('dashboard.profile.edit'));

        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_merchant_can_choose_a_plan(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create([
            'name' => 'Starter',
            'slug' => 'starter-test',
            'is_active' => true,
            'monthly_price' => '29.00',
        ]);

        $this->actingAs($user)
            ->post(route('dashboard.plans.store'), ['plan_id' => $plan->id])
            ->assertRedirect(route('dashboard.plans.index'));

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);
    }

    public function test_credentials_are_hidden_until_email_is_verified(): void
    {
        $user = User::factory()->unverified()->create();
        $site = Site::factory()->for($user)->create();
        $token = app(SiteTokenService::class)->tokenFor($site);
        $secret = app(SiteSecretService::class)->secretFor($site);

        $this->actingAs($user)
            ->get(route('dashboard.credentials'))
            ->assertOk()
            ->assertSee('hidden until you verify')
            ->assertDontSee($token)
            ->assertDontSee($secret);
    }

    public function test_verified_merchant_can_view_credentials(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->for($user)->create();
        $token = app(SiteTokenService::class)->tokenFor($site);
        $secret = app(SiteSecretService::class)->secretFor($site);

        $this->actingAs($user)
            ->get(route('dashboard.credentials'))
            ->assertOk()
            ->assertSee('API URL')
            ->assertSee($site->uuid)
            ->assertSee($token)
            ->assertSee($secret)
            ->assertSee(rtrim((string) config('app.url'), '/').'/api/v1');
    }
}
