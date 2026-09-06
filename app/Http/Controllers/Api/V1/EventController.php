<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UsageEventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\IngestEventRequest;
use App\Models\Conversation;
use App\Models\Site;
use App\Models\UsageEvent;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function store(IngestEventRequest $request): JsonResponse
    {
        $site = $request->attributes->get('site');
        assert($site instanceof Site);

        $conversationId = null;

        if ($request->filled('conversation_id')) {
            $conversationId = Conversation::query()
                ->forSite($site)
                ->where('uuid', $request->string('conversation_id')->toString())
                ->value('id');
        }

        UsageEvent::query()->create([
            'site_id' => $site->id,
            'user_id' => $site->user_id,
            'conversation_id' => $conversationId,
            'type' => UsageEventType::tryFrom($request->string('type')->toString()) ?? UsageEventType::Tokens,
            'units' => $request->integer('units', 1),
            'metadata' => $request->input('metadata'),
        ]);

        return response()->json(['accepted' => true], 202);
    }
}
