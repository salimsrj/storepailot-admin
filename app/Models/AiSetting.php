<?php

namespace App\Models;

use App\Enums\AiProvider;
use Database\Factories\AiSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'provider',
    'openai_api_key',
    'openai_organization',
    'gemini_api_key',
    'model',
    'timeout',
    'max_tool_iterations',
    'max_context_messages',
    'greeting_phrases',
    'greeting_reply',
])]
#[Hidden(['openai_api_key', 'gemini_api_key'])]
class AiSetting extends Model
{
    /** @use HasFactory<AiSettingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'provider' => AiProvider::class,
            'openai_api_key' => 'encrypted',
            'gemini_api_key' => 'encrypted',
            'timeout' => 'integer',
            'max_tool_iterations' => 'integer',
            'max_context_messages' => 'integer',
            'greeting_phrases' => 'array',
        ];
    }

    public function greetingPhrasesText(): string
    {
        $phrases = $this->greeting_phrases;

        if (! is_array($phrases) || $phrases === []) {
            /** @var list<string> $defaults */
            $defaults = config('commercepilot.greetings.phrases', []);

            return implode("\n", $defaults);
        }

        return implode("\n", $phrases);
    }

    public function maskedApiKey(): ?string
    {
        return $this->maskKey($this->openai_api_key);
    }

    public function maskedGeminiApiKey(): ?string
    {
        return $this->maskKey($this->gemini_api_key);
    }

    private function maskKey(mixed $key): ?string
    {
        if (! is_string($key) || $key === '') {
            return null;
        }

        $suffix = substr($key, -4);

        return '••••'.$suffix;
    }
}
