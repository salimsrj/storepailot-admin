<?php

namespace App\Services\Chat;

use App\Enums\ConversationMode;
use App\Enums\MessageRole;
use App\Exceptions\AgentModeException;
use App\Models\Conversation;
use App\Models\Message;

/**
 * Moves a conversation between AI and human control, and records agent replies.
 */
class HandoverService
{
    public function takeOver(Conversation $conversation, ?string $agent = null): Conversation
    {
        $conversation->forceFill([
            'mode' => ConversationMode::Human,
            'handover_at' => now(),
            'handover_by' => $agent,
        ])->save();

        return $conversation;
    }

    public function release(Conversation $conversation): Conversation
    {
        $conversation->loadMissing('site.settings', 'site.user.currentSubscription');
        $settings = $conversation->site?->settings;
        $hasSubscription = $conversation->site?->user?->currentSubscription !== null;

        if (! $settings?->enable_agent || ! $hasSubscription) {
            throw AgentModeException::disabled();
        }

        $conversation->forceFill([
            'mode' => ConversationMode::Ai,
            'handover_at' => null,
            'handover_by' => null,
        ])->save();

        return $conversation;
    }

    /**
     * Stored as an assistant turn so the AI keeps the thread coherent if it
     * later resumes; the metadata marks it as written by a person.
     */
    public function reply(Conversation $conversation, string $content, ?string $agent = null): Message
    {
        $message = $conversation->messages()->create([
            'role' => MessageRole::Assistant,
            'content' => $content,
            'metadata' => array_filter([
                'author' => 'human',
                'author_name' => $agent,
            ]),
        ]);

        $conversation->forceFill([
            'message_count' => $conversation->messages()->count(),
            'last_message_at' => now(),
        ])->save();

        return $message;
    }
}
