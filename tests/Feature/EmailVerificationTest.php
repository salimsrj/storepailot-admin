<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_verify_email_from_signed_link_and_is_sent_to_login(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'verify@example.com',
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ],
        );

        $this->get($url)
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_logged_in_user_is_redirected_to_credentials_after_verify(): void
    {
        $user = User::factory()->unverified()->create();
        Site::factory()->for($user)->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ],
        );

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect(route('dashboard.credentials'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_invalid_hash_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1('wrong-email'),
            ],
        );

        $this->get($url)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_email_can_be_resent_when_logged_in(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create([
            'email' => 'ada@example.com',
        ]);

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect();

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
