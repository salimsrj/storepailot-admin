<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MerchantAuthTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_page_is_available(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in to your account');
    }

    public function test_merchant_can_sign_in_and_reach_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'merchant@example.com',
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'email' => 'merchant@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_admin_cannot_use_merchant_login(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'admin@example.com',
                'password' => 'password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_admin_cannot_access_merchant_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('dashboard'))->assertForbidden();
    }

    public function test_merchant_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_merchant_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
