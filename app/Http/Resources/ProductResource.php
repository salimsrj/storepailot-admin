<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'] ?? null,
            'name' => $this['name'] ?? null,
            'price' => $this['price'] ?? null,
            'currency' => $this['currency'] ?? null,
            'stock_status' => $this['stock_status'] ?? null,
            'image' => $this['image'] ?? null,
            'url' => $this['url'] ?? null,
            'short_description' => $this['short_description'] ?? null,
            'categories' => $this['categories'] ?? [],
            'has_variations' => $this['has_variations'] ?? false,
        ];
    }
}
