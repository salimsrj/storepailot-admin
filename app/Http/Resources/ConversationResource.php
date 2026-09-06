<?php

namespace App\Http\Resources;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @mixin Conversation
 */
class ConversationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'status' => $this->status->value,
            'mode' => $this->mode->value,
            'handover_at' => $this->handover_at?->toIso8601String(),
            'handover_by' => $this->handover_by,
            'visitor_id' => $this->whenLoaded('visitor', fn () => $this->visitor->uuid),
            'summary' => $this->summary,
            'message_count' => $this->message_count,
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'last_message' => $this->whenLoaded('lastMessage', fn () => $this->lastMessage ? [
                'role' => $this->lastMessage->role->value,
                'preview' => Str::limit($this->lastMessage->content, 120),
            ] : null),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
        ];
    }
}
