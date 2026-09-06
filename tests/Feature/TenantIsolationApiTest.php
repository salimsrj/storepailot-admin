<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Site;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TenantIsolationApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_site_a_cannot_read_site_b_conversation(): void
    {
        $siteA = $this->authenticatedSite();
        $siteB = Site::factory()->withSettings()->create();
        $conversationB = Conversation::factory()->for($siteB)->create();
        $this->fakeAI();

        $this->postJson('/api/v1/chat', [
            'conversation_id' => $conversationB->uuid,
            'visitor_id' => '11111111-1111-1111-1111-111111111111',
            'message' => 'Hello',
        ], $this->siteHeaders())
            ->assertNotFound();

        $this->assertFalse($siteA->conversations()->whereKey($conversationB)->exists());
    }

    public function test_user_a_cannot_register_against_user_b_identity(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->postJson('/api/v1/sites', [
                'name' => 'Stolen Store',
                'url' => 'https://stolen.example',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Stolen Store');

        $this->assertSame(0, $owner->sites()->count());
        $this->assertSame(1, $intruder->sites()->count());
    }

    public function test_visitor_cannot_continue_another_visitors_conversation(): void
    {
        $site = $this->authenticatedSite();
        $visitorA = Visitor::factory()->for($site)->create();
        $visitorB = Visitor::factory()->for($site)->create();
        $conversation = Conversation::factory()->for($site)->for($visitorA)->create();
        $this->fakeAI();

        $this->postJson('/api/v1/chat', [
            'conversation_id' => $conversation->uuid,
            'visitor_id' => $visitorB->uuid,
            'message' => 'Hi',
        ], $this->siteHeaders())
            ->assertForbidden();
    }
}
