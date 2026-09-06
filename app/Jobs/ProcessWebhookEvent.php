<?php

namespace App\Jobs;

use App\Services\Subscription\SubscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessWebhookEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $provider,
        public string $eventId,
        public string $eventType,
        public array $payload,
    ) {}

    public function handle(SubscriptionService $subscriptions): void
    {
        if ($this->eventId === '') {
            return;
        }

        $subscriptions->handleWebhook(
            $this->provider,
            $this->eventId,
            $this->eventType,
            $this->payload,
        );
    }
}
