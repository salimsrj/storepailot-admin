<?php

namespace Tests\Feature;

use App\Enums\ConversationStatus;
use App\Enums\MessageRole;
use App\Enums\SiteStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserStatus;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Plan;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\Subscription;
use App\Models\UsageEvent;
use App\Models\UsagePeriod;
use App\Models\User;
use App\Models\Visitor;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_receives_a_public_uuid_and_active_status(): void
    {
        $user = User::factory()->create();

        $this->assertNotEmpty($user->uuid);
        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertSame('uuid', $user->getRouteKeyName());
    }

    public function test_user_owns_sites_and_subscriptions(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->starter()->create();
        $site = Site::factory()->for($user)->create();
        $subscription = Subscription::factory()->for($user)->for($plan)->create();

        $this->assertTrue($user->sites()->whereKey($site)->exists());
        $this->assertTrue($user->subscriptions()->whereKey($subscription)->exists());
        $this->assertTrue($user->currentSubscription()->whereKey($subscription)->exists());
        $this->assertSame($user->id, $site->user_id);
        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
    }

    public function test_current_subscription_ignores_cancelled_subscriptions(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();

        Subscription::factory()->for($user)->for($plan)->cancelled()->create();
        $active = Subscription::factory()->for($user)->for($plan)->create();

        $this->assertTrue($user->currentSubscription()->whereKey($active)->exists());
        $this->assertSame(1, $user->currentSubscription()->count());
    }

    public function test_site_has_exactly_one_settings_record(): void
    {
        $site = Site::factory()->withSettings()->create();

        $this->assertSame(1, $site->settings()->count());
        $this->assertSame('CommercePilot', $site->settings()->value('assistant_name'));
        $this->assertSame(SiteStatus::Active, $site->status);
    }

    public function test_site_hides_token_hash_and_encrypted_secret(): void
    {
        $site = Site::factory()->create();

        $this->assertArrayNotHasKey('site_token_hash', $site->toArray());
        $this->assertArrayNotHasKey('site_token_encrypted', $site->toArray());
        $this->assertArrayNotHasKey('site_secret_encrypted', $site->toArray());
        $this->assertNotEmpty($site->site_token_hash);
        $this->assertNotEmpty($site->site_token_encrypted);
        $this->assertNotEmpty($site->site_secret_encrypted);
        $this->assertSame(64, strlen($site->site_token_hash));
    }

    public function test_conversation_belongs_to_site_and_visitor_and_has_messages(): void
    {
        $site = Site::factory()->create();
        $visitor = Visitor::factory()->for($site)->create();
        $conversation = Conversation::factory()->for($site)->for($visitor)->create();
        $message = Message::factory()->for($conversation)->create([
            'role' => MessageRole::User,
            'content' => 'Show me black running shoes',
        ]);

        $this->assertTrue($site->conversations()->whereKey($conversation)->exists());
        $this->assertTrue($visitor->conversations()->whereKey($conversation)->exists());
        $this->assertTrue($conversation->messages()->whereKey($message)->exists());
        $this->assertSame($site->id, $conversation->site_id);
        $this->assertSame($visitor->id, $conversation->visitor_id);
        $this->assertSame(ConversationStatus::Open, $conversation->status);
        $this->assertNull($message->updated_at);
    }

    public function test_usage_period_belongs_to_the_site_owner_and_calculates_remaining_messages(): void
    {
        $site = Site::factory()->create();
        $period = UsagePeriod::factory()->for($site)->nearlyExhausted()->create();

        $this->assertSame($site->user_id, $period->user_id);
        $this->assertSame(1, $period->remainingMessages());
        $this->assertTrue($site->usagePeriods()->whereKey($period)->exists());
        $this->assertTrue($site->user()->first()->usagePeriods()->whereKey($period)->exists());
    }

    public function test_usage_event_can_belong_to_a_conversation_on_the_same_site(): void
    {
        $conversation = Conversation::factory()->create();
        $event = UsageEvent::factory()->forConversation($conversation)->create();

        $this->assertSame($conversation->site_id, $event->site_id);
        $this->assertSame($conversation->id, $event->conversation_id);
        $this->assertTrue($conversation->site()->first()->usageEvents()->whereKey($event)->exists());
    }

    public function test_site_setting_factory_creates_a_single_settings_row(): void
    {
        $settings = SiteSetting::factory()->create();

        $this->assertTrue($settings->site()->exists());
        $this->assertSame(1, SiteSetting::query()->where('site_id', $settings->site_id)->count());
    }

    public function test_webhook_event_can_be_marked_processed(): void
    {
        $event = WebhookEvent::factory()->create();

        $this->assertNull($event->processed_at);

        $event->markProcessed();

        $this->assertNotNull($event->fresh()->processed_at);
    }
}
