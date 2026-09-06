<?php

namespace App\Jobs;

use App\Models\Conversation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CleanupExpiredConversations implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $days = (int) config('commercepilot.retention.conversation_days');

        Conversation::query()
            ->where('updated_at', '<', now()->subDays($days))
            ->orderBy('id')
            ->chunkById(100, function ($conversations): void {
                $conversations->each->delete();
            });
    }
}
