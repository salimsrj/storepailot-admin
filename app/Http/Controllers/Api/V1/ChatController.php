<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatRequest;
use App\Http\Resources\ChatResponseResource;
use App\Models\Site;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function store(ChatRequest $request, ChatService $chat): ChatResponseResource
    {
        $site = $request->attributes->get('site');
        assert($site instanceof Site);

        return new ChatResponseResource($chat->reply($site, $request->validated()));
    }

    public function stream(ChatRequest $request, ChatService $chat): StreamedResponse|JsonResponse
    {
        $site = $request->attributes->get('site');
        assert($site instanceof Site);

        $result = $chat->reply($site, $request->validated());
        $content = $result['message']?->content ?? '';

        return response()->stream(function () use ($result, $content): void {
            $this->emit('message.start', []);
            $this->emit('message.delta', ['content' => $content]);
            $this->emit('message.complete', [
                'conversation_id' => $result['conversation']->uuid,
                'mode' => $result['conversation']->mode->value,
                'content' => $content,
                'usage' => $result['usage']->toArray(),
            ]);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function emit(string $event, array $data): void
    {
        echo 'event: '.$event."\n";
        echo 'data: '.json_encode($data, JSON_THROW_ON_ERROR)."\n\n";

        if (function_exists('ob_flush')) {
            @ob_flush();
        }

        flush();
    }
}
