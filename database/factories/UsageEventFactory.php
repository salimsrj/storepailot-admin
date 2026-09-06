<?php

namespace Database\Factories;

use App\Enums\UsageEventType;
use App\Models\Conversation;
use App\Models\Site;
use App\Models\UsageEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsageEvent>
 */
class UsageEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => null,
            'type' => UsageEventType::Message,
            'units' => 1,
            'input_tokens' => 100,
            'output_tokens' => 40,
            'provider' => 'openai',
            'model' => null,
            'metadata' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (UsageEvent $event): void {
            if (! $event->site_id) {
                $site = Site::factory()->create();
                $event->site()->associate($site);
                $event->user()->associate($site->user);
            } elseif (! $event->user_id) {
                $event->user_id = Site::query()->findOrFail($event->site_id)->user_id;
            }

            if ($event->model === null) {
                $event->model = config('commercepilot.ai.model');
            }
        });
    }

    public function forConversation(Conversation $conversation): static
    {
        return $this->state(fn (array $attributes) => [
            'site_id' => $conversation->site_id,
            'user_id' => $conversation->site()->value('user_id'),
            'conversation_id' => $conversation->id,
        ]);
    }
}
