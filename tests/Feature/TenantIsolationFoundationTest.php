<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Site;
use App\Models\UsagePeriod;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TenantIsolationFoundationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_sites_do_not_include_another_users_sites(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ownedSite = Site::factory()->for($owner)->create(['name' => 'Owned Store']);
        $foreignSite = Site::factory()->for($other)->create(['name' => 'Foreign Store']);

        $this->assertTrue($owner->sites()->whereKey($ownedSite)->exists());
        $this->assertFalse($owner->sites()->whereKey($foreignSite)->exists());
        $this->assertTrue(Site::query()->forUser($owner)->whereKey($ownedSite)->exists());
        $this->assertFalse(Site::query()->forUser($owner)->whereKey($foreignSite)->exists());
    }

    public function test_site_conversations_do_not_include_another_sites_conversations(): void
    {
        $siteA = Site::factory()->create();
        $siteB = Site::factory()->create();
        $conversationA = Conversation::factory()->for($siteA)->create();
        $conversationB = Conversation::factory()->for($siteB)->create();

        $this->assertTrue($siteA->conversations()->whereKey($conversationA)->exists());
        $this->assertFalse($siteA->conversations()->whereKey($conversationB)->exists());
        $this->assertTrue(Conversation::query()->forSite($siteA)->whereKey($conversationA)->exists());
        $this->assertFalse(Conversation::query()->forSite($siteA)->whereKey($conversationB)->exists());
    }

    public function test_visitor_conversations_do_not_include_another_visitors_conversations(): void
    {
        $site = Site::factory()->create();
        $visitorA = Visitor::factory()->for($site)->create();
        $visitorB = Visitor::factory()->for($site)->create();
        $conversationA = Conversation::factory()->for($site)->for($visitorA)->create();
        $conversationB = Conversation::factory()->for($site)->for($visitorB)->create();

        $this->assertTrue($visitorA->conversations()->whereKey($conversationA)->exists());
        $this->assertFalse($visitorA->conversations()->whereKey($conversationB)->exists());
        $this->assertTrue(Conversation::query()->forVisitor($visitorA)->whereKey($conversationA)->exists());
        $this->assertFalse(Conversation::query()->forVisitor($visitorA)->whereKey($conversationB)->exists());
    }

    public function test_usage_periods_stay_scoped_to_their_site(): void
    {
        $siteA = Site::factory()->create();
        $siteB = Site::factory()->create();
        $periodA = UsagePeriod::factory()->for($siteA)->create();
        $periodB = UsagePeriod::factory()->for($siteB)->create();

        $this->assertTrue($siteA->usagePeriods()->whereKey($periodA)->exists());
        $this->assertFalse($siteA->usagePeriods()->whereKey($periodB)->exists());
        $this->assertFalse($siteA->user()->first()->usagePeriods()->whereKey($periodB)->exists());
    }
}
