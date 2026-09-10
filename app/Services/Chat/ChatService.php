<?php

namespace App\Services\Chat;

use App\AI\Agent\AgentContext;
use App\AI\Agent\CommerceAgent;
use App\AI\Contracts\AIProviderInterface;
use App\Enums\ConversationMode;
use App\Enums\MessageRole;
use App\Exceptions\AIProviderException;
use App\Exceptions\CommercePilotException;
use App\Exceptions\ConversationException;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\Visitor;
use App\Services\Usage\UsageService;
use App\Services\Usage\UsageSnapshot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class ChatService
{
    public function __construct(
        private CommerceAgent $agent,
        private AIProviderInterface $provider,
        private UsageService $usage,
        private CannedReplyService $canned,
    ) {}

    /**
     * @param  array{conversation_id?: string|null, visitor_id: string, message: string}  $payload
     * @return array{conversation: Conversation, message: Message|null, products: list<array<string, mixed>>, usage: UsageSnapshot}
     */
    public function reply(Site $site, array $payload): array
    {
        $settings = $this->settingsFor($site);
        $visitor = $this->resolveVisitor($site, $payload['visitor_id']);
        $conversation = $this->resolveConversation($site, $visitor, $payload['conversation_id'] ?? null);

        // A human agent has taken this conversation over: record the visitor's
        // message and stop. No usage is reserved and no AI provider is called.
        if ($conversation->mode === ConversationMode::Human) {
            $this->storeVisitorMessage($conversation, $payload['message']);

            return [
                'conversation' => $conversation->fresh() ?? $conversation,
                'message' => null,
                'products' => [],
                'usage' => $this->usage->snapshot($site),
            ];
        }

        if ($this->canned->matches($payload['message'])) {
            $message = $this->canned->respond($conversation, $settings, $payload['message']);

            return [
                'conversation' => $conversation->fresh() ?? $conversation,
                'message' => $message,
                'products' => [],
                'usage' => $this->usage->snapshot($site),
            ];
        }

        $this->usage->reserveMessage($site);

        try {
            $result = $this->agent->handle(new AgentContext(
                site: $site,
                settings: $settings,
                visitor: $visitor,
                conversation: $conversation->fresh() ?? $conversation,
                message: $payload['message'],
            ));
        } catch (CommercePilotException $exception) {
            $this->usage->releaseReservation($site);

            throw $exception;
        } catch (Throwable $exception) {
            $this->usage->releaseReservation($site);

            report($exception);

            throw AIProviderException::unavailable();
        }

        $this->usage->recordUsage(
            $site,
            $conversation,
            $result['response']->inputTokens,
            $result['response']->outputTokens,
            $this->provider->name(),
            $result['response']->model,
        );

        return [
            'conversation' => $conversation->fresh() ?? $conversation,
            'message' => $result['message'],
            'products' => $result['products'],
            'usage' => $this->usage->snapshot($site),
        ];
    }

    private function storeVisitorMessage(Conversation $conversation, string $content): Message
    {
        $message = $conversation->messages()->create([
            'role' => MessageRole::User,
            'content' => $content,
        ]);

        $conversation->forceFill([
            'message_count' => $conversation->messages()->count(),
            'last_message_at' => now(),
        ])->save();

        return $message;
    }

    private function settingsFor(Site $site): SiteSetting
    {
        Cache::forget('site-settings:'.$site->id);

        return $site->settings()->firstOrCreate([], [
            'assistant_name' => 'CommercePilot',
            'welcome_message' => 'Hi! How can I help you find the right product today?',
            'language' => 'en',
            'tone' => 'helpful',
        ]);
    }

    private function resolveVisitor(Site $site, string $visitorUuid): Visitor
    {
        $visitor = Visitor::query()
            ->forSite($site)
            ->where('uuid', $visitorUuid)
            ->first();

        if ($visitor) {
            $visitor->forceFill(['last_seen_at' => now()])->save();

            return $visitor;
        }

        $visitor = new Visitor([
            'site_id' => $site->id,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);
        $visitor->uuid = $visitorUuid;
        $visitor->forceFill([
            'visitor_token_hash' => hash('sha256', Str::random(40)),
        ])->save();

        return $visitor;
    }

    private function resolveConversation(Site $site, Visitor $visitor, ?string $conversationUuid): Conversation
    {
        if ($conversationUuid === null) {
            return Conversation::query()->create([
                'site_id' => $site->id,
                'visitor_id' => $visitor->id,
            ]);
        }

        $conversation = Conversation::query()
            ->forSite($site)
            ->where('uuid', $conversationUuid)
            ->first();

        if ($conversation === null) {
            throw ConversationException::notFound();
        }

        if ($conversation->visitor_id !== $visitor->id) {
            throw ConversationException::forbidden();
        }

        return $conversation;
    }
}
