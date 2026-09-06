<?php

namespace App\Services\WooCommerce\DTOs;

readonly class CartData
{
    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function __construct(
        public array $items,
        public string $total,
        public string $currency,
        public int $itemCount,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            items: array_values($payload['items'] ?? []),
            total: (string) ($payload['total'] ?? '0.00'),
            currency: (string) ($payload['currency'] ?? 'USD'),
            itemCount: (int) ($payload['item_count'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'total' => $this->total,
            'currency' => $this->currency,
            'item_count' => $this->itemCount,
        ];
    }
}
