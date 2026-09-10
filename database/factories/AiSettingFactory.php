<?php

namespace Database\Factories;

use App\Enums\AiProvider;
use App\Models\AiSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiSetting>
 */
class AiSettingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => AiProvider::OpenAi,
            'openai_api_key' => null,
            'openai_organization' => null,
            'gemini_api_key' => null,
            'model' => 'gpt-4.1-mini',
            'timeout' => 30,
            'max_tool_iterations' => 5,
            'max_context_messages' => 20,
            'greeting_phrases' => config('commercepilot.greetings.phrases'),
            'greeting_reply' => null,
        ];
    }

    public function withApiKey(string $key = 'sk-test-admin-key'): static
    {
        return $this->state(fn (array $attributes) => [
            'openai_api_key' => $key,
        ]);
    }

    public function gemini(string $key = 'gemini-test-admin-key'): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => AiProvider::Gemini,
            'gemini_api_key' => $key,
            'model' => 'gemini-3.8-flash',
        ]);
    }
}
