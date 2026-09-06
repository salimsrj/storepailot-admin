<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResponseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'conversation_id' => $this['conversation']->uuid,
            'mode' => $this['conversation']->mode->value,
            'message' => $this['message'] ? new MessageResource($this['message']) : null,
            'products' => ProductResource::collection($this['products']),
            'usage' => new UsageResource($this['usage']),
        ];
    }
}
