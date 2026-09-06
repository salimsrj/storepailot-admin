<?php

namespace App\Jobs;

use App\AI\Contracts\AIProviderInterface;
use App\AI\DTOs\AIRequest;
use App\Enums\MessageRole;
use App\Models\Conversation;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SummarizeConversation implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 3600;

    public function __construct(public int $conversationId) {}

    public function uniqueId(): string
    {
        return (string) $this->conversationId;
    }

    public function handle(AIProviderInterface $provider): void
    {
        $conversation = Conversation::query()->find($this->conversationId);

        if ($conversation === null) {
            return;
        }

        $excerpt = $conversation->messages()
            ->whereIn('role', [MessageRole::User, MessageRole::Assistant])
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->reverse()
            ->map(fn ($message): string => $message->role->value.': '.$message->content)
            ->implode("\n");

        try {
            $response = $provider->complete(new AIRequest([
                [
                    'role' => 'system',
                    'content' => 'Summarize this shopping conversation in 3 short sentences. Include product preferences and cart actions. Do not invent facts.',
                ],
                [
                    'role' => 'user',
                    'content' => $excerpt,
                ],
            ]));
        } catch (Throwable) {
            return;
        }

        if (filled($response->content)) {
            $conversation->forceFill([
                'summary' => $response->content,
            ])->save();
        }
    }
}
