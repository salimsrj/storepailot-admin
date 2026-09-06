<?php

namespace Database\Factories;

use App\Enums\ConversationStatus;
use App\Models\Conversation;
use App\Models\Site;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => ConversationStatus::Open,
            'summary' => null,
            'message_count' => 0,
            'last_message_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Conversation $conversation): void {
            if (! $conversation->site_id) {
                $conversation->site()->associate(Site::factory()->create());
            }

            if (! $conversation->visitor_id) {
                $site = $conversation->relationLoaded('site') && $conversation->site
                    ? $conversation->site
                    : Site::query()->findOrFail($conversation->site_id);

                $conversation->visitor()->associate(
                    Visitor::factory()->for($site)->create()
                );
            }
        });
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ConversationStatus::Closed,
        ]);
    }
}
