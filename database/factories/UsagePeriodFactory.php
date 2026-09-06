<?php

namespace Database\Factories;

use App\Models\Site;
use App\Models\UsagePeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsagePeriod>
 */
class UsagePeriodFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'message_limit' => 50,
            'message_count' => 0,
            'input_tokens' => 0,
            'output_tokens' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (UsagePeriod $period): void {
            if (! $period->site_id) {
                $site = Site::factory()->create();
                $period->site()->associate($site);
                $period->user_id = $site->user_id;
            } elseif (! $period->user_id) {
                $period->user_id = Site::query()->findOrFail($period->site_id)->user_id;
            }
        });
    }

    public function nearlyExhausted(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_limit' => 50,
            'message_count' => 49,
        ]);
    }

    public function exhausted(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_limit' => 50,
            'message_count' => 50,
        ]);
    }
}
