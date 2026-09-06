<?php

namespace App\Services\Usage;

use App\Enums\SubscriptionStatus;
use App\Enums\UsageEventType;
use App\Events\UsageLimitReached;
use App\Exceptions\UsageLimitException;
use App\Models\Conversation;
use App\Models\Site;
use App\Models\Subscription;
use App\Models\UsageEvent;
use App\Models\UsagePeriod;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UsageService
{
    public function currentPeriod(Site $site): UsagePeriod
    {
        $start = now()->startOfMonth()->toDateString();
        $existing = $this->periodFor($site, $start);

        if ($existing) {
            return $existing;
        }

        try {
            return UsagePeriod::query()->create([
                'site_id' => $site->id,
                'user_id' => $site->user_id,
                'period_start' => $start,
                'period_end' => now()->endOfMonth()->toDateString(),
                'message_limit' => $this->getLimit($site),
                'message_count' => 0,
                'input_tokens' => 0,
                'output_tokens' => 0,
            ]);
        } catch (UniqueConstraintViolationException) {
            return $this->periodFor($site, $start)
                ?? UsagePeriod::query()
                    ->where('site_id', $site->id)
                    ->whereDate('period_start', $start)
                    ->firstOrFail();
        }
    }

    private function periodFor(Site $site, string $start): ?UsagePeriod
    {
        return UsagePeriod::query()
            ->where('site_id', $site->id)
            ->whereDate('period_start', $start)
            ->first();
    }

    public function getLimit(Site $site): int
    {
        $cacheKey = 'plan-limit:'.$site->user_id;

        return (int) Cache::remember($cacheKey, now()->addMinutes(5), function () use ($site): int {
            $subscription = Subscription::query()
                ->where('user_id', $site->user_id)
                ->whereIn('status', [
                    SubscriptionStatus::Trialing,
                    SubscriptionStatus::Active,
                    SubscriptionStatus::PastDue,
                ])
                ->with('plan')
                ->latest('id')
                ->first();

            return $subscription?->plan?->monthly_messages ?? 50;
        });
    }

    public function getUsed(Site $site): int
    {
        return $this->currentPeriod($site)->message_count;
    }

    public function getRemaining(Site $site): int
    {
        $period = $this->currentPeriod($site);

        return max(0, $period->message_limit - $period->message_count);
    }

    public function snapshot(Site $site): UsageSnapshot
    {
        $period = $this->currentPeriod($site);

        return new UsageSnapshot(
            used: $period->message_count,
            limit: $period->message_limit,
            remaining: max(0, $period->message_limit - $period->message_count),
        );
    }

    public function hasAvailableMessage(Site $site): bool
    {
        return $this->getRemaining($site) > 0;
    }

    public function reserveMessage(Site $site): UsagePeriod
    {
        $this->currentPeriod($site);

        return DB::transaction(function () use ($site): UsagePeriod {
            $period = UsagePeriod::query()
                ->where('site_id', $site->id)
                ->whereDate('period_start', now()->startOfMonth()->toDateString())
                ->lockForUpdate()
                ->firstOrFail();

            if ($period->message_count >= $period->message_limit) {
                event(new UsageLimitReached($site));

                throw UsageLimitException::reached();
            }

            $period->forceFill([
                'message_count' => $period->message_count + 1,
            ])->save();

            return $period->refresh();
        });
    }

    public function recordUsage(
        Site $site,
        Conversation $conversation,
        int $inputTokens,
        int $outputTokens,
        string $provider,
        string $model,
    ): void {
        DB::transaction(function () use ($site, $conversation, $inputTokens, $outputTokens, $provider, $model): void {
            $period = UsagePeriod::query()
                ->where('site_id', $site->id)
                ->whereDate('period_start', now()->startOfMonth()->toDateString())
                ->lockForUpdate()
                ->firstOrFail();

            $period->forceFill([
                'input_tokens' => $period->input_tokens + $inputTokens,
                'output_tokens' => $period->output_tokens + $outputTokens,
            ])->save();

            UsageEvent::query()->create([
                'site_id' => $site->id,
                'user_id' => $site->user_id,
                'conversation_id' => $conversation->id,
                'type' => UsageEventType::Message,
                'units' => 1,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'provider' => $provider,
                'model' => $model,
            ]);
        });
    }

    public function releaseReservation(Site $site): void
    {
        DB::transaction(function () use ($site): void {
            $period = UsagePeriod::query()
                ->where('site_id', $site->id)
                ->whereDate('period_start', now()->startOfMonth()->toDateString())
                ->lockForUpdate()
                ->first();

            if ($period === null || $period->message_count < 1) {
                return;
            }

            $period->forceFill([
                'message_count' => $period->message_count - 1,
            ])->save();

            UsageEvent::query()->create([
                'site_id' => $site->id,
                'user_id' => $site->user_id,
                'conversation_id' => null,
                'type' => UsageEventType::ReservationReleased,
                'units' => 1,
            ]);
        });
    }

    public function resetPeriod(Site $site, ?Carbon $month = null): UsagePeriod
    {
        $month ??= now();

        return UsagePeriod::query()->updateOrCreate(
            [
                'site_id' => $site->id,
                'period_start' => $month->copy()->startOfMonth()->toDateString(),
            ],
            [
                'user_id' => $site->user_id,
                'period_end' => $month->copy()->endOfMonth()->toDateString(),
                'message_limit' => $this->getLimit($site),
                'message_count' => 0,
                'input_tokens' => 0,
                'output_tokens' => 0,
            ],
        );
    }
}
