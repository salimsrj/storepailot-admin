<?php

namespace App\Services\Ai;

use App\Models\AiSetting;
use Illuminate\Support\Facades\Cache;

class AiSettingsService
{
    private ?AiSetting $current = null;

    public function current(): AiSetting
    {
        if ($this->current instanceof AiSetting) {
            return $this->current;
        }

        Cache::forget('ai-settings');

        return $this->current = AiSetting::query()->firstOrCreate([], [
            'openai_organization' => config('services.openai.organization'),
            'model' => config('commercepilot.ai.model'),
            'timeout' => (int) config('commercepilot.ai.timeout'),
            'max_tool_iterations' => (int) config('commercepilot.ai.max_tool_iterations'),
            'max_context_messages' => (int) config('commercepilot.ai.max_context_messages'),
            'greeting_phrases' => config('commercepilot.greetings.phrases'),
        ]);
    }

    public function apiKey(): ?string
    {
        $stored = $this->current()->openai_api_key;
        $fallback = config('services.openai.api_key');

        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }

    public function organization(): ?string
    {
        $stored = $this->current()->openai_organization;
        $fallback = config('services.openai.organization');

        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }

    public function model(): string
    {
        return $this->current()->model ?: (string) config('commercepilot.ai.model');
    }

    public function timeout(): int
    {
        return $this->current()->timeout ?: (int) config('commercepilot.ai.timeout');
    }

    public function maxToolIterations(): int
    {
        return $this->current()->max_tool_iterations ?: (int) config('commercepilot.ai.max_tool_iterations');
    }

    public function maxContextMessages(): int
    {
        return $this->current()->max_context_messages ?: (int) config('commercepilot.ai.max_context_messages');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(array $attributes): AiSetting
    {
        $settings = $this->current();

        if (! filled($attributes['openai_api_key'] ?? null)) {
            unset($attributes['openai_api_key']);
        }

        $settings->fill($attributes)->save();

        return $this->current = $settings->refresh();
    }
}
