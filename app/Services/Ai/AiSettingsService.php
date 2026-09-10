<?php

namespace App\Services\Ai;

use App\Enums\AiProvider;
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
            'provider' => $this->configuredProvider()->value,
            'openai_organization' => config('services.openai.organization'),
            'model' => config('commercepilot.ai.model'),
            'timeout' => (int) config('commercepilot.ai.timeout'),
            'max_tool_iterations' => (int) config('commercepilot.ai.max_tool_iterations'),
            'max_context_messages' => (int) config('commercepilot.ai.max_context_messages'),
            'greeting_phrases' => config('commercepilot.greetings.phrases'),
        ]);
    }

    public function provider(): AiProvider
    {
        return $this->current()->provider ?? $this->configuredProvider();
    }

    public function apiKey(): ?string
    {
        return $this->provider() === AiProvider::Gemini
            ? $this->geminiApiKey()
            : $this->openaiApiKey();
    }

    public function openaiApiKey(): ?string
    {
        return $this->firstFilled(
            $this->current()->openai_api_key,
            config('services.openai.api_key'),
        );
    }

    public function geminiApiKey(): ?string
    {
        return $this->firstFilled(
            $this->current()->gemini_api_key,
            config('services.gemini.api_key'),
        );
    }

    public function organization(): ?string
    {
        return $this->firstFilled(
            $this->current()->openai_organization,
            config('services.openai.organization'),
        );
    }

    public function model(): string
    {
        $model = $this->current()->model ?: (string) config('commercepilot.ai.model');

        return $this->modelFor($this->provider(), $model);
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

        if (! filled($attributes['gemini_api_key'] ?? null)) {
            unset($attributes['gemini_api_key']);
        }

        if (isset($attributes['provider']) || isset($attributes['model'])) {
            $provider = AiProvider::tryFrom((string) ($attributes['provider'] ?? $settings->provider?->value))
                ?? $this->provider();
            $model = (string) ($attributes['model'] ?? $settings->model);
            $attributes['model'] = $this->modelFor($provider, $model);
        }

        $settings->fill($attributes)->save();

        return $this->current = $settings->refresh();
    }

    private function configuredProvider(): AiProvider
    {
        return AiProvider::tryFrom((string) config('commercepilot.ai.provider'))
            ?? AiProvider::OpenAi;
    }

    private function firstFilled(mixed $stored, mixed $fallback): ?string
    {
        if (is_string($stored)) {
            $stored = trim($stored);
        }

        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        if (is_string($fallback)) {
            $fallback = trim($fallback);
        }

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }

    public function modelFor(AiProvider $provider, string $model): string
    {
        $model = trim($model);

        if ($provider === AiProvider::Gemini && ($this->looksLikeOpenAiModel($model) || $this->isLegacyGeminiModel($model))) {
            return (string) config('commercepilot.ai.gemini_model');
        }

        if ($provider === AiProvider::OpenAi && $this->looksLikeGeminiModel($model)) {
            return (string) config('commercepilot.ai.model');
        }

        if ($model !== '') {
            return $model;
        }

        return $provider === AiProvider::Gemini
            ? (string) config('commercepilot.ai.gemini_model')
            : (string) config('commercepilot.ai.model');
    }

    private function looksLikeOpenAiModel(string $model): bool
    {
        return (bool) preg_match('/^(gpt-|o1|o3|chatgpt)/i', $model);
    }

    private function isLegacyGeminiModel(string $model): bool
    {
        return (bool) preg_match('/^gemini-([12][.-]|3\.6[.-])/i', $model);
    }

    private function looksLikeGeminiModel(string $model): bool
    {
        return str_starts_with(strtolower($model), 'gemini-');
    }
}
