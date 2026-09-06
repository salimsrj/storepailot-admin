<?php

namespace App\Billing\Providers;

use App\Billing\Contracts\PaymentProviderInterface;
use App\Enums\PaymentProvider;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Str;

class ManualPaymentProvider implements PaymentProviderInterface
{
    public function createCustomer(User $user): array
    {
        return [
            'customer_id' => 'cus_manual_'.$user->id,
        ];
    }

    public function createSubscription(User $user, Plan $plan, ?string $customerId = null): array
    {
        return [
            'subscription_id' => 'sub_manual_'.Str::uuid(),
            'customer_id' => $customerId ?? $this->createCustomer($user)['customer_id'],
            'status' => SubscriptionStatus::Active->value,
        ];
    }

    public function cancelSubscription(Subscription $subscription, bool $atPeriodEnd = true): void
    {
        $subscription->forceFill([
            'cancel_at_period_end' => $atPeriodEnd,
            'status' => $atPeriodEnd ? $subscription->status : SubscriptionStatus::Cancelled,
        ])->save();
    }

    public function changePlan(Subscription $subscription, Plan $plan): array
    {
        $subscription->forceFill([
            'plan_id' => $plan->id,
        ])->save();

        return [
            'subscription_id' => (string) $subscription->provider_subscription_id,
            'status' => $subscription->status->value,
        ];
    }

    public function getSubscription(Subscription $subscription): array
    {
        return [
            'id' => $subscription->provider_subscription_id,
            'status' => $subscription->status->value,
            'provider' => PaymentProvider::Manual->value,
        ];
    }

    public function verifyWebhook(array $payload, ?string $signature = null): bool
    {
        return isset($payload['event_id'], $payload['event_type']);
    }
}
