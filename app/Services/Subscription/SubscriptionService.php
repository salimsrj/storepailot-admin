<?php

namespace App\Services\Subscription;

use App\Billing\Contracts\PaymentProviderInterface;
use App\Enums\PaymentProvider;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function __construct(private PaymentProviderInterface $provider) {}

    public function activate(User $user, Plan $plan): Subscription
    {
        $remote = $this->provider->createSubscription($user, $plan);

        $subscription = Subscription::query()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'provider' => PaymentProvider::Manual,
            'provider_customer_id' => $remote['customer_id'],
            'provider_subscription_id' => $remote['subscription_id'],
            'status' => SubscriptionStatus::from($remote['status']),
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        Cache::forget('plan-limit:'.$user->id);

        Log::channel('billing')->info('subscription.activated', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => $subscription->status->value,
        ]);

        return $subscription;
    }

    public function cancel(Subscription $subscription, bool $atPeriodEnd = true): Subscription
    {
        $this->provider->cancelSubscription($subscription, $atPeriodEnd);

        $subscription->forceFill([
            'cancel_at_period_end' => $atPeriodEnd,
            'status' => $atPeriodEnd ? $subscription->status : SubscriptionStatus::Cancelled,
            'ends_at' => $atPeriodEnd ? $subscription->ends_at : now(),
        ])->save();

        Cache::forget('plan-limit:'.$subscription->user_id);

        return $subscription->refresh();
    }

    public function changePlan(Subscription $subscription, Plan $plan): Subscription
    {
        $this->provider->changePlan($subscription, $plan);

        $subscription->forceFill([
            'plan_id' => $plan->id,
        ])->save();

        Cache::forget('plan-limit:'.$subscription->user_id);

        return $subscription->refresh();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleWebhook(string $provider, string $eventId, string $eventType, array $payload): WebhookEvent
    {
        return DB::transaction(function () use ($provider, $eventId, $eventType, $payload): WebhookEvent {
            $existing = WebhookEvent::query()
                ->where('provider', $provider)
                ->where('event_id', $eventId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $event = WebhookEvent::query()->create([
                'provider' => $provider,
                'event_id' => $eventId,
                'event_type' => $eventType,
                'payload' => $payload,
            ]);

            $this->applyWebhook($event);
            $event->markProcessed();

            return $event->refresh();
        });
    }

    private function applyWebhook(WebhookEvent $event): void
    {
        $subscriptionId = data_get($event->payload, 'subscription_id');

        if (! is_string($subscriptionId) || $subscriptionId === '') {
            return;
        }

        $subscription = Subscription::query()
            ->where('provider_subscription_id', $subscriptionId)
            ->first();

        if ($subscription === null) {
            return;
        }

        $status = match ($event->event_type) {
            'subscription.activated', 'invoice.paid' => SubscriptionStatus::Active,
            'subscription.cancelled' => SubscriptionStatus::Cancelled,
            'subscription.past_due', 'invoice.payment_failed' => SubscriptionStatus::PastDue,
            'subscription.expired' => SubscriptionStatus::Expired,
            default => null,
        };

        if ($status) {
            $subscription->forceFill(['status' => $status])->save();
            Cache::forget('plan-limit:'.$subscription->user_id);
        }

        if ($event->event_type === 'subscription.upgraded' || $event->event_type === 'subscription.downgraded') {
            $planSlug = data_get($event->payload, 'plan_slug');
            $plan = is_string($planSlug) ? Plan::query()->where('slug', $planSlug)->first() : null;

            if ($plan) {
                $subscription->forceFill(['plan_id' => $plan->id])->save();
                Cache::forget('plan-limit:'.$subscription->user_id);
            }
        }
    }
}
