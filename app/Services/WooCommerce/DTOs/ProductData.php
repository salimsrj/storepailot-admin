<?php

namespace App\Services\WooCommerce\DTOs;

readonly class ProductData
{
    /**
     * @param  list<string>  $categories
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $price,
        public string $currency,
        public string $stockStatus,
        public ?string $image,
        public string $url,
        public ?string $shortDescription,
        public array $categories,
        public bool $hasVariations,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            id: (int) ($payload['id'] ?? 0),
            name: (string) ($payload['name'] ?? ''),
            price: (string) ($payload['price'] ?? ''),
            currency: (string) ($payload['currency'] ?? 'USD'),
            stockStatus: (string) ($payload['stock_status'] ?? 'outofstock'),
            image: isset($payload['image']) ? (string) $payload['image'] : null,
            url: (string) ($payload['url'] ?? ''),
            shortDescription: isset($payload['short_description']) ? (string) $payload['short_description'] : null,
            categories: array_values(array_map('strval', $payload['categories'] ?? [])),
            hasVariations: (bool) ($payload['has_variations'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'currency' => $this->currency,
            'stock_status' => $this->stockStatus,
            'image' => $this->image,
            'url' => $this->url,
            'short_description' => $this->shortDescription,
            'categories' => $this->categories,
            'has_variations' => $this->hasVariations,
        ];
    }
}
