<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BillingWebhookTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_subscription_activation_creates_an_active_subscription(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->starter()->create();

        $subscription = app(SubscriptionService::class)->activate($user, $plan);

        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
        $this->assertSame($plan->id, $subscription->plan_id);
        $this->assertNotNull($subscription->provider_subscription_id);
    }

    public function test_cancellation_marks_the_subscription_cancelled(): void
    {
        $subscription = Subscription::factory()->create();

        $updated = app(SubscriptionService::class)->cancel($subscription, false);

        $this->assertSame(SubscriptionStatus::Cancelled, $updated->status);
    }

    public function test_upgrade_and_downgrade_change_the_plan(): void
    {
        $starter = Plan::factory()->starter()->create();
        $business = Plan::factory()->business()->create();
        $subscription = Subscription::factory()->for($starter)->create();

        $upgraded = app(SubscriptionService::class)->changePlan($subscription, $business);
        $this->assertSame($business->id, $upgraded->plan_id);

        $downgraded = app(SubscriptionService::class)->changePlan($upgraded, $starter);
        $this->assertSame($starter->id, $downgraded->plan_id);
    }

    public function test_duplicate_webhook_is_not_processed_twice(): void
    {
        $subscription = Subscription::factory()->create([
            'provider_subscription_id' => 'sub_123',
            'status' => SubscriptionStatus::Incomplete,
        ]);
        $service = app(SubscriptionService::class);
        $payload = [
            'provider' => 'manual',
            'event_id' => 'evt_dup_1',
            'event_type' => 'subscription.activated',
            'subscription_id' => 'sub_123',
        ];

        $first = $service->handleWebhook('manual', 'evt_dup_1', 'subscription.activated', $payload);
        $second = $service->handleWebhook('manual', 'evt_dup_1', 'subscription.activated', $payload);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, WebhookEvent::query()->count());
        $this->assertSame(SubscriptionStatus::Active, $subscription->refresh()->status);
    }

    public function test_invalid_webhook_returns_401(): void
    {
        $this->postJson('/api/v1/webhooks/billing', [
            'type' => 'missing-required-fields',
        ])->assertUnauthorized()
            ->assertJsonPath('error.code', 'invalid_webhook');
    }

    public function test_valid_webhook_is_accepted(): void
    {
        $this->postJson('/api/v1/webhooks/billing', [
            'provider' => 'manual',
            'event_id' => 'evt_ok_1',
            'event_type' => 'subscription.activated',
            'subscription_id' => 'sub_missing',
        ])->assertAccepted();
    }
}
