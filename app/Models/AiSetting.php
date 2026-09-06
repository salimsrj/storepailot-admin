<?php

namespace App\Models;

use Database\Factories\AiSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'openai_api_key',
    'openai_organization',
    'model',
    'timeout',
    'max_tool_iterations',
    'max_context_messages',
    'greeting_phrases',
    'greeting_reply',
])]
#[Hidden(['openai_api_key'])]
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
            'openai_api_key' => 'encrypted',
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
        $key = $this->openai_api_key;

        if (! is_string($key) || $key === '') {
            return null;
        }

        $suffix = substr($key, -4);

        return '••••'.$suffix;
    }
}
