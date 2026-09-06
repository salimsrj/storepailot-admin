<?php

namespace Database\Factories;

use App\Models\Site;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Visitor>
 */
class VisitorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Visitor $visitor): void {
            if (blank($visitor->visitor_token_hash)) {
                $visitor->forceFill([
                    'visitor_token_hash' => hash('sha256', Str::random(40)),
                ]);
            }
        });
    }
}
