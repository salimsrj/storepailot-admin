<?php

namespace Tests\Feature;

use App\Exceptions\UsageLimitException;
use App\Models\Plan;
use App\Models\Site;
use App\Models\Subscription;
use App\Models\UsagePeriod;
use App\Services\Usage\UsageService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UsageServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_free_plan_exposes_a_50_message_limit(): void
    {
        $site = $this->siteOnPlan('free', 50);

        $this->assertSame(50, app(UsageService::class)->getLimit($site));
        $this->assertSame(50, app(UsageService::class)->getRemaining($site));
    }

    public function test_premium_plan_exposes_its_database_limit(): void
    {
        $site = $this->siteOnPlan('starter', 1000);

        $this->assertSame(1000, app(UsageService::class)->getLimit($site));
    }

    public function test_reserve_message_increments_used_count(): void
    {
        $site = $this->siteOnPlan('free', 50);
        $usage = app(UsageService::class);

        $usage->reserveMessage($site);

        $this->assertSame(1, $usage->getUsed($site));
        $this->assertSame(49, $usage->getRemaining($site));
    }

    public function test_limit_reached_does_not_consume_another_message(): void
    {
        $site = $this->siteOnPlan('free', 50);
        UsagePeriod::factory()->for($site)->exhausted()->create();

        try {
            app(UsageService::class)->reserveMessage($site);
            $this->fail('The exhausted period reserved another message.');
        } catch (UsageLimitException) {
            $this->assertSame(50, app(UsageService::class)->getUsed($site));
        }
    }

    public function test_only_one_of_ten_attempts_consumes_the_final_quota_slot(): void
    {
        $site = $this->siteOnPlan('free', 50);
        UsagePeriod::factory()->for($site)->nearlyExhausted()->create();
        $usage = app(UsageService::class);

        $successes = 0;
        $failures = 0;

        for ($i = 0; $i < 10; $i++) {
            try {
                $usage->reserveMessage($site);
                $successes++;
            } catch (UsageLimitException) {
                $failures++;
            }
        }

        $this->assertSame(1, $successes);
        $this->assertSame(9, $failures);
        $this->assertSame(50, $usage->getUsed($site));
        $this->assertSame(0, $usage->getRemaining($site));
    }

    public function test_release_reservation_restores_the_quota_slot(): void
    {
        $site = $this->siteOnPlan('free', 50);
        $usage = app(UsageService::class);
        $usage->reserveMessage($site);

        $usage->releaseReservation($site);

        $this->assertSame(0, $usage->getUsed($site));
    }

    private function siteOnPlan(string $slug, int $messages): Site
    {
        $site = Site::factory()->withSettings()->create();
        $plan = Plan::factory()->create([
            'slug' => $slug,
            'monthly_messages' => $messages,
        ]);
        Subscription::factory()->for($site->user()->first())->for($plan)->create();

        return $site;
    }
}
