<?php

namespace Database\Factories;

use App\Enums\PaymentProvider;
use App\Models\WebhookEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookEvent>
 */
class WebhookEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => PaymentProvider::Stripe,
            'event_id' => 'evt_'.fake()->unique()->uuid(),
            'event_type' => 'customer.subscription.updated',
            'payload' => [
                'id' => 'evt_example',
                'type' => 'customer.subscription.updated',
            ],
            'processed_at' => null,
        ];
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'processed_at' => now(),
        ]);
    }
}
