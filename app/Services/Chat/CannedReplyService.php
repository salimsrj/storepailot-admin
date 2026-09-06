<?php

namespace App\Services\Chat;

use App\Enums\MessageRole;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\SiteSetting;
use App\Services\Ai\AiSettingsService;

class CannedReplyService
{
    public function __construct(private AiSettingsService $aiSettings) {}

    public function matches(string $message): bool
    {
        $normalized = $this->normalize($message);

        if ($normalized === '') {
            return false;
        }

        return in_array($normalized, $this->phrases(), true);
    }

    /**
     * @return list<string>
     */
    public function phrases(): array
    {
        $stored = $this->aiSettings->current()->greeting_phrases;
        $raw = is_array($stored) && $stored !== []
            ? $stored
            : config('commercepilot.greetings.phrases', []);

        $phrases = [];

        foreach ($raw as $phrase) {
            if (! is_string($phrase)) {
                continue;
            }

            $normalized = $this->normalize($phrase);

            if ($normalized !== '') {
                $phrases[] = $normalized;
            }
        }

        return array_values(array_unique($phrases));
    }

    public function replyFor(SiteSetting $settings): string
    {
        $override = $this->aiSettings->current()->greeting_reply;

        if (is_string($override) && trim($override) !== '') {
            return trim($override);
        }

        $welcome = $settings->welcome_message;

        if (is_string($welcome) && trim($welcome) !== '') {
            return trim($welcome);
        }

        $name = is_string($settings->assistant_name) && $settings->assistant_name !== ''
            ? $settings->assistant_name
            : 'CommercePilot';

        return "Hi! I'm {$name}. How can I help you find the right product today?";
    }

    public function respond(Conversation $conversation, SiteSetting $settings, string $userMessage): Message
    {
        $conversation->messages()->create([
            'role' => MessageRole::User,
            'content' => $userMessage,
        ]);

        $assistant = $conversation->messages()->create([
            'role' => MessageRole::Assistant,
            'content' => $this->replyFor($settings),
        ]);

        $conversation->forceFill([
            'message_count' => $conversation->messages()->count(),
            'last_message_at' => now(),
        ])->save();

        return $assistant;
    }

    public function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', '', $text) ?? '';
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }
}
