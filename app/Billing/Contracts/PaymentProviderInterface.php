<?php

namespace App\Billing\Contracts;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

interface PaymentProviderInterface
{
    /**
     * @return array{customer_id: string}
     */
    public function createCustomer(User $user): array;

    /**
     * @return array{subscription_id: string, customer_id: string, status: string}
     */
    public function createSubscription(User $user, Plan $plan, ?string $customerId = null): array;

    public function cancelSubscription(Subscription $subscription, bool $atPeriodEnd = true): void;

    /**
     * @return array{subscription_id: string, status: string}
     */
    public function changePlan(Subscription $subscription, Plan $plan): array;

    /**
     * @return array<string, mixed>
     */
    public function getSubscription(Subscription $subscription): array;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function verifyWebhook(array $payload, ?string $signature = null): bool;
}
