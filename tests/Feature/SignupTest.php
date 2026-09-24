<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SignupTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_signup_page_is_available_at_root(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Create your CommercePilot account');
    }

    public function test_signup_requires_website_url_name_email_and_password(): void
    {
        $this->from(route('signup.create'))
            ->post(route('signup.store'), [])
            ->assertRedirect(route('signup.create'))
            ->assertSessionHasErrors(['website_url', 'first_name', 'last_name', 'email', 'password']);
    }

    public function test_signup_creates_user_and_site_then_redirects_to_login(): void
    {
        Notification::fake();

        $response = $this->post(route('signup.store'), [
            'website_url' => 'https://shop.example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('status')
            ->assertSessionMissing('signup.credentials');

        $user = User::query()->where('email', 'ada@example.com')->firstOrFail();

        $this->assertSame('Ada', $user->first_name);
        $this->assertSame('Lovelace', $user->last_name);
        $this->assertSame('Ada Lovelace', $user->name);
        $this->assertFalse($user->is_admin);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue($user->sites()->exists());

        $site = Site::query()->whereBelongsTo($user)->firstOrFail();
        $this->assertSame('https://shop.example.com', $site->url);
        $this->assertNotEmpty($site->site_token_encrypted);

        Notification::assertSentTo($user, VerifyEmail::class);

        $this->assertGuest();
    }

    public function test_signup_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->from(route('signup.create'))
            ->post(route('signup.store'), [
                'website_url' => 'https://shop.example.com',
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'email' => 'taken@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('signup.create'))
            ->assertSessionHasErrors(['email']);
    }
}
