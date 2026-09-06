<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_are_redirected_to_the_admin_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_login_page_is_available(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Sign in to the admin dashboard');
    }

    public function test_non_admin_cannot_access_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_sign_in_and_view_the_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@commercepilot.test',
            'password' => 'password',
        ]);

        $this->post('/admin/login', [
            'email' => 'admin@commercepilot.test',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Dashboard');
    }

    public function test_admin_can_create_a_plan(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.plans.store'), [
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'monthly_messages' => 5000,
            'max_sites' => 25,
            'monthly_price' => '99.00',
            'currency' => 'USD',
            'is_active' => '1',
        ])->assertRedirect(route('admin.plans.index'));

        $this->assertDatabaseHas('plans', [
            'slug' => 'enterprise',
            'monthly_messages' => 5000,
        ]);
    }

    public function test_admin_can_create_a_user_and_site(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Merchant',
            'email' => 'merchant@example.com',
            'password' => 'password123',
            'status' => 'active',
            'is_admin' => '0',
        ])->assertRedirect(route('admin.users.index'));

        $merchant = User::query()->where('email', 'merchant@example.com')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.sites.store'), [
            'user_id' => $merchant->id,
            'name' => 'Fashion Store',
            'url' => 'https://fashion.example',
        ])->assertRedirect()->assertSessionHas('site_token');

        $this->assertDatabaseHas('sites', [
            'name' => 'Fashion Store',
            'user_id' => $merchant->id,
        ]);
    }

    public function test_admin_can_open_management_indexes(): void
    {
        $admin = User::factory()->admin()->create();
        Plan::factory()->create();
        Site::factory()->withSettings()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk();
        $this->actingAs($admin)
            ->get(route('admin.plans.index'))
            ->assertOk();
        $this->actingAs($admin)
            ->get(route('admin.subscriptions.index'))
            ->assertOk();
        $this->actingAs($admin)
            ->get(route('admin.sites.index'))
            ->assertOk();
        $this->actingAs($admin)
            ->get(route('admin.conversations.index'))
            ->assertOk();
        $this->actingAs($admin)
            ->get(route('admin.usage-periods.index'))
            ->assertOk();
        $this->actingAs($admin)
            ->get(route('admin.usage-events.index'))
            ->assertOk();
        $this->actingAs($admin)
            ->get(route('admin.webhooks.index'))
            ->assertOk();
    }
}
