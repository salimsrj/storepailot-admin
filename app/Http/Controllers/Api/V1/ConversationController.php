<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ConversationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AgentReplyRequest;
use App\Http\Requests\ConversationQueryRequest;
use App\Http\Requests\HandoverRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Site;
use App\Services\Chat\HandoverService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Site-scoped read and handover access to conversations, used by the
 * WordPress admin inbox. Every lookup is constrained to the authenticated site.
 */
class ConversationController extends Controller
{
    private const DEFAULT_MESSAGE_LIMIT = 200;

    public function __construct(private HandoverService $handover) {}

    public function index(ConversationQueryRequest $request): AnonymousResourceCollection
    {
        $site = $this->site($request);
        $perPage = (int) ($request->validated('per_page') ?? 25);
        $mode = $request->validated('mode');

        $conversations = Conversation::query()
            ->forSite($site)
            ->with(['visitor', 'lastMessage'])
            ->when($mode !== null, fn ($query) => $query->where('mode', $mode))
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return ConversationResource::collection($conversations);
    }

    /**
     * Lightweight count for the WordPress admin menu badge: open conversations
     * where the visitor's message is still unanswered.
     */
    public function waitingCount(ConversationQueryRequest $request): JsonResponse
    {
        $count = Conversation::query()
            ->forSite($this->site($request))
            ->waitingReply()
            ->count();

        return response()->json([
            'data' => [
                'waiting_count' => $count,
            ],
        ]);
    }

    public function show(ConversationQueryRequest $request, string $conversation): ConversationResource
    {
        $model = $this->find($this->site($request), $conversation);
        $model->load('visitor');
        $model->setRelation('messages', $this->messagesFor($model, $request));

        return new ConversationResource($model);
    }

    /**
     * Incremental message fetch. Both the admin inbox and the storefront widget
     * poll this; when a visitor_id is supplied, ownership is enforced so a
     * visitor can never read someone else's thread.
     */
    public function messages(ConversationQueryRequest $request, string $conversation): JsonResponse
    {
        $model = $this->find($this->site($request), $conversation);
        $visitorUuid = $request->validated('visitor_id');

        if ($visitorUuid !== null) {
            $this->assertVisitorOwns($model, $visitorUuid);
        }

        return response()->json([
            'data' => [
                'conversation_id' => $model->uuid,
                'mode' => $model->mode->value,
                'messages' => MessageResource::collection($this->messagesFor($model, $request))->resolve(),
            ],
        ]);
    }

    public function takeOver(HandoverRequest $request, string $conversation): ConversationResource
    {
        $model = $this->find($this->site($request), $conversation);

        return new ConversationResource(
            $this->handover->takeOver($model, $request->validated('agent'))
        );
    }

    public function release(HandoverRequest $request, string $conversation): ConversationResource
    {
        $model = $this->find($this->site($request), $conversation);

        return new ConversationResource($this->handover->release($model));
    }

    public function reply(AgentReplyRequest $request, string $conversation): JsonResponse
    {
        $model = $this->find($this->site($request), $conversation);

        $message = $this->handover->reply(
            $model,
            (string) $request->validated('content'),
            $request->validated('agent'),
        );

        return response()->json([
            'data' => (new MessageResource($message))->resolve(),
        ], 201);
    }

    private function site(FormRequest $request): Site
    {
        $site = $request->attributes->get('site');
        assert($site instanceof Site);

        return $site;
    }

    private function find(Site $site, string $uuid): Conversation
    {
        $conversation = Conversation::query()
            ->forSite($site)
            ->where('uuid', $uuid)
            ->first();

        if ($conversation === null) {
            throw ConversationException::notFound();
        }

        return $conversation;
    }

    private function assertVisitorOwns(Conversation $conversation, string $visitorUuid): void
    {
        $visitor = $conversation->visitor;

        if ($visitor === null || $visitor->uuid !== $visitorUuid) {
            throw ConversationException::forbidden();
        }
    }

    /**
     * @return Collection<int, Message>
     */
    private function messagesFor(Conversation $conversation, ConversationQueryRequest $request): Collection
    {
        $limit = (int) ($request->validated('limit') ?? self::DEFAULT_MESSAGE_LIMIT);
        $afterId = $request->validated('after_id');

        if ($afterId !== null) {
            // Everything since the caller's cursor, oldest first.
            return $conversation->messages()
                ->where('id', '>', (int) $afterId)
                ->orderBy('id')
                ->limit($limit)
                ->get();
        }

        // Newest slice, flipped back into reading order.
        return $conversation->messages()
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }
}
