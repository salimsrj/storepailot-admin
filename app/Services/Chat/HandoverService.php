<?php

namespace App\Services\Chat;

use App\Enums\ConversationMode;
use App\Enums\MessageRole;
use App\Exceptions\AgentModeException;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\WooCommerce\DTOs\ProductData;

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
     *
     * @param  list<array<string, mixed>>  $products
     */
    public function reply(Conversation $conversation, string $content, ?string $agent = null, array $products = []): Message
    {
        $normalized = $this->normalizeProducts($products);
        $content = trim($content);

        // Keep a readable transcript when the agent shares cards without typing.
        if ($content === '' && $normalized !== []) {
            $content = implode(', ', array_map(
                static fn (array $product): string => (string) ($product['name'] ?? 'Product'),
                $normalized,
            ));
        }

        $metadata = array_filter([
            'author' => 'human',
            'author_name' => $agent,
            'products' => $normalized !== [] ? $normalized : null,
        ], static fn ($value) => $value !== null && $value !== '');

        $message = $conversation->messages()->create([
            'role' => MessageRole::Assistant,
            'content' => $content,
            'metadata' => $metadata,
        ]);

        $conversation->forceFill([
            'message_count' => $conversation->messages()->count(),
            'last_message_at' => now(),
        ])->save();

        return $message;
    }

    /**
     * @param  list<array<string, mixed>>  $products
     * @return list<array<string, mixed>>
     */
    private function normalizeProducts(array $products): array
    {
        $out = [];

        foreach (array_slice($products, 0, 5) as $product) {
            if (! is_array($product)) {
                continue;
            }

            $normalized = ProductData::fromArray($product)->toArray();
            if ($normalized['id'] < 1) {
                continue;
            }

            $out[] = $normalized;
        }

        return $out;
    }
}
