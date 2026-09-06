<?php

namespace App\Http\Resources;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Message
 */
class MessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $metadata = is_array($this->metadata) ? $this->metadata : [];
        $author = isset($metadata['author']) && $metadata['author'] === 'human' ? 'human' : 'ai';

        return [
            'id' => $this->id,
            'role' => $this->role->value,
            'content' => $this->content,
            'author' => $author,
            'author_name' => isset($metadata['author_name']) ? (string) $metadata['author_name'] : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
