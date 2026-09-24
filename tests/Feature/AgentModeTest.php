<?php

namespace Tests\Feature;

use App\AI\DTOs\AIResponse;
use App\Enums\ConversationMode;
use App\Models\Conversation;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AgentModeTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_agent_mode_off_stores_message_without_ai_or_usage(): void
    {
        $site = $this->authenticatedSite();
        $this->assertFalse($site->settings->enable_agent);

        $provider = $this->fakeAI();
        $provider->push(new AIResponse('should not be used', [], 1, 1, 'fake-model'));

        $this->postJson('/api/v1/chat', [
            'visitor_id' => '11111111-1111-1111-1111-111111111111',
            'message' => 'Do you have size 10?',
        ], $this->siteHeaders())
            ->assertOk()
            ->assertJsonPath('data.message', null)
            ->assertJsonPath('data.usage.used', 0);

        $this->assertCount(1, $provider->queue);
        $this->assertDatabaseHas('messages', [
            'role' => 'user',
            'content' => 'Do you have size 10?',
        ]);
        $this->assertDatabaseMissing('messages', [
            'role' => 'assistant',
            'content' => 'should not be used',
        ]);
    }

    public function test_agent_mode_on_with_subscription_calls_the_ai(): void
    {
        $this->enableAgentMode($this->authenticatedSite());
        $this->fakeAI();

        $this->postJson('/api/v1/chat', [
            'visitor_id' => '11111111-1111-1111-1111-111111111111',
            'message' => 'Show me black running shoes',
        ], $this->siteHeaders())
            ->assertOk()
            ->assertJsonPath('data.message.role', 'assistant')
            ->assertJsonPath('data.usage.used', 1);
    }

    public function test_enabling_agent_without_subscription_is_rejected(): void
    {
        $site = $this->authenticatedSite();

        $this->patchJson('/api/v1/site/settings', [
            'enable_agent' => true,
        ], $this->siteHeaders())
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'agent_subscription_required');

        $this->assertFalse($site->settings()->first()->fresh()->enable_agent);
    }

    public function test_enabling_agent_with_subscription_succeeds(): void
    {
        $site = $this->authenticatedSite();
        Subscription::factory()->for($site->user)->create();

        $this->patchJson('/api/v1/site/settings', [
            'enable_agent' => true,
        ], $this->siteHeaders())
            ->assertOk()
            ->assertJsonPath('data.enable_agent', true);

        $this->assertTrue($site->settings()->first()->fresh()->enable_agent);
    }

    public function test_site_resource_exposes_agent_flags(): void
    {
        $site = $this->enableAgentMode($this->authenticatedSite());

        $this->getJson('/api/v1/site', $this->siteHeaders())
            ->assertOk()
            ->assertJsonPath('data.enable_agent', true)
            ->assertJsonPath('data.can_enable_agent', true)
            ->assertJsonPath('data.subscription.status', 'active');

        $site->settings()->update(['enable_agent' => false]);

        $this->getJson('/api/v1/site', $this->siteHeaders())
            ->assertOk()
            ->assertJsonPath('data.enable_agent', false)
            ->assertJsonPath('data.can_enable_agent', true);
    }

    public function test_release_to_ai_is_blocked_when_agent_mode_is_off(): void
    {
        $site = $this->authenticatedSite();
        $conversation = Conversation::factory()->for($site)->create([
            'mode' => ConversationMode::Human,
        ]);

        $path = "/api/v1/conversations/{$conversation->uuid}/release";
        $body = (string) json_encode([]);

        $this->postJson($path, [], $this->siteHeaders(extra: $this->signedHeaders(
            body: $body,
            method: 'POST',
            path: $path,
        )))
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'agent_mode_disabled');

        $this->assertSame(ConversationMode::Human, $conversation->fresh()->mode);
    }
}
