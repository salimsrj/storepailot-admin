<?php

namespace App\Services\Usage;

readonly class UsageSnapshot
{
    public function __construct(
        public int $used,
        public int $limit,
        public int $remaining,
    ) {}

    /**
     * @return array{used: int, limit: int, remaining: int}
     */
    public function toArray(): array
    {
        return [
            'used' => $this->used,
            'limit' => $this->limit,
            'remaining' => $this->remaining,
        ];
    }
}
