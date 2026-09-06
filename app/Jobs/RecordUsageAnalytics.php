<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Models\Site;
use App\Services\Usage\UsageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordUsageAnalytics implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public int $siteId,
        public int $conversationId,
        public int $inputTokens,
        public int $outputTokens,
        public string $model,
    ) {}

    public function handle(UsageService $usage): void
    {
        $site = Site::query()->find($this->siteId);
        $conversation = Conversation::query()->find($this->conversationId);

        if ($site === null || $conversation === null) {
            return;
        }

        // Token totals are already recorded synchronously. This job is a
        // durable analytics hook for later reporting pipelines.
        $usage->snapshot($site);
    }
}
