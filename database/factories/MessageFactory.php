<?php

namespace Database\Factories;

use App\Enums\MessageRole;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'role' => MessageRole::User,
            'content' => fake()->sentence(),
            'tool_name' => null,
            'tool_call_id' => null,
            'input_tokens' => null,
            'output_tokens' => null,
            'provider_response_id' => null,
            'metadata' => null,
        ];
    }

    public function assistant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MessageRole::Assistant,
            'content' => 'I found a few options that match what you are looking for.',
            'input_tokens' => 120,
            'output_tokens' => 48,
            'provider_response_id' => 'resp_'.fake()->uuid(),
        ]);
    }

    public function tool(string $name = 'search_products'): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MessageRole::Tool,
            'tool_name' => $name,
            'tool_call_id' => 'call_'.fake()->uuid(),
            'content' => '{"results":[]}',
        ]);
    }
}
