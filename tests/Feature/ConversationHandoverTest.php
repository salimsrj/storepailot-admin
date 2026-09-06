<?php

namespace Tests\Feature;

use App\AI\DTOs\AIResponse;
use App\Enums\ConversationMode;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ConversationHandoverTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * Headers for a signed site request, bound to this exact method and path.
     *
     * @param  array<string, mixed>|null  $payload
     * @return array<string, string>
     */
    private function signedFor(string $method, string $path, ?array $payload = null): array
    {
        $body = $payload === null ? '' : (string) json_encode($payload);

        return $this->siteHeaders(extra: $this->signedHeaders($body, method: $method, path: $path));
    }

    /**
     * A signed GET with no request body, matching what the plugin sends.
     * getJson() would transmit "[]" as the body and break the signature.
     *
     * @param  array<string, string>|null  $headers
     */
    private function getSigned(string $path, ?array $headers = null): TestResponse
    {
        $headers ??= $this->signedFor('GET', $path);

        return $this->call('GET', $path, [], [], [], $this->transformHeadersToServerVars(
            $headers + ['Accept' => 'application/json']
        ));
    }

    public function test_human_mode_stores_the_message_without_calling_the_ai(): void
    {
        $site = $this->authenticatedSite();
        $visitor = Visitor::factory()->for($site)->create();
        $conversation = Conversation::factory()->for($site)->for($visitor)->create([
            'mode' => ConversationMode::Human,
        ]);

        $provider = $this->fakeAI();
        $provider->push(new AIResponse('should not be used', [], 1, 1, 'fake-model'));

        $this->postJson('/api/v1/chat', [
            'conversation_id' => $conversation->uuid,
            'visitor_id' => $visitor->uuid,
            'message' => 'Is this in stock?',
        ], $this->siteHeaders())
            ->assertOk()
            ->assertJsonPath('data.mode', 'human')
            ->assertJsonPath('data.message', null);

        // The AI provider was never consulted.
        $this->assertCount(1, $provider->queue);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Is this in stock?',
        ]);
        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
        ]);
    }

    public function test_human_mode_does_not_consume_usage(): void
    {
        $site = $this->authenticatedSite();
        $visitor = Visitor::factory()->for($site)->create();
        $conversation = Conversation::factory()->for($site)->for($visitor)->create([
            'mode' => ConversationMode::Human,
        ]);
        $this->fakeAI();

        $this->postJson('/api/v1/chat', [
            'conversation_id' => $conversation->uuid,
            'visitor_id' => $visitor->uuid,
            'message' => 'Hello there, I need help choosing',
        ], $this->siteHeaders())
            ->assertOk()
            ->assertJsonPath('data.usage.used', 0);
    }

    public function test_ai_mode_still_answers_normally(): void
    {
        $site = $this->authenticatedSite();
        $visitor = Visitor::factory()->for($site)->create();
        $conversation = Conversation::factory()->for($site)->for($visitor)->create();
        $this->fakeAI();

        $this->postJson('/api/v1/chat', [
            'conversation_id' => $conversation->uuid,
            'visitor_id' => $visitor->uuid,
            'message' => 'Show me black running shoes',
        ], $this->siteHeaders())
            ->assertOk()
            ->assertJsonPath('data.mode', 'ai')
            ->assertJsonPath('data.message.role', 'assistant');
    }

    public function test_takeover_and_release_toggle_the_mode(): void
    {
        $site = $this->authenticatedSite();
        $conversation = Conversation::factory()->for($site)->create();

        $takeoverPath = "/api/v1/conversations/{$conversation->uuid}/takeover";
        $this->postJson($takeoverPath, [], $this->signedFor('POST', $takeoverPath, []))
            ->assertOk()
            ->assertJsonPath('data.mode', 'human');

        $this->assertSame(ConversationMode::Human, $conversation->fresh()->mode);
        $this->assertNotNull($conversation->fresh()->handover_at);

        $releasePath = "/api/v1/conversations/{$conversation->uuid}/release";
        $this->postJson($releasePath, [], $this->signedFor('POST', $releasePath, []))
            ->assertOk()
            ->assertJsonPath('data.mode', 'ai');

        $this->assertSame(ConversationMode::Ai, $conversation->fresh()->mode);
        $this->assertNull($conversation->fresh()->handover_at);
    }

    public function test_agent_reply_is_stored_as_a_human_authored_assistant_message(): void
    {
        $site = $this->authenticatedSite();
        $conversation = Conversation::factory()->for($site)->create([
            'mode' => ConversationMode::Human,
        ]);

        $path = "/api/v1/conversations/{$conversation->uuid}/messages";
        $payload = ['content' => 'Yes, we have it in medium.', 'agent' => 'Store Owner'];

        $this->postJson($path, $payload, $this->signedFor('POST', $path, $payload))
            ->assertCreated()
            ->assertJsonPath('data.role', 'assistant')
            ->assertJsonPath('data.author', 'human')
            ->assertJsonPath('data.author_name', 'Store Owner');

        $message = Message::query()->where('conversation_id', $conversation->id)->sole();
        $this->assertSame('human', $message->metadata['author']);
    }

    public function test_conversations_are_scoped_to_the_authenticated_site(): void
    {
        $site = $this->authenticatedSite();
        $mine = Conversation::factory()->for($site)->create();
        $foreign = Conversation::factory()->create();

        $response = $this->getSigned('/api/v1/conversations')->assertOk();

        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($mine->uuid, $ids);
        $this->assertNotContains($foreign->uuid, $ids);
    }

    public function test_another_sites_conversation_cannot_be_read(): void
    {
        $this->authenticatedSite();
        $foreign = Conversation::factory()->create();

        $path = "/api/v1/conversations/{$foreign->uuid}";
        $this->getSigned($path)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'conversation_not_found');
    }

    public function test_messages_endpoint_rejects_a_mismatched_visitor(): void
    {
        $site = $this->authenticatedSite();
        $owner = Visitor::factory()->for($site)->create();
        $other = Visitor::factory()->for($site)->create();
        $conversation = Conversation::factory()->for($site)->for($owner)->create();

        $path = "/api/v1/conversations/{$conversation->uuid}/messages?visitor_id={$other->uuid}";
        $this->getSigned($path)
            ->assertForbidden()
            ->assertJsonPath('error.code', 'conversation_forbidden');
    }

    public function test_messages_endpoint_returns_only_messages_after_the_cursor(): void
    {
        $site = $this->authenticatedSite();
        $visitor = Visitor::factory()->for($site)->create();
        $conversation = Conversation::factory()->for($site)->for($visitor)->create();

        $first = $conversation->messages()->create(['role' => 'user', 'content' => 'first']);
        $second = $conversation->messages()->create(['role' => 'assistant', 'content' => 'second']);

        $path = "/api/v1/conversations/{$conversation->uuid}/messages?after_id={$first->id}&visitor_id={$visitor->uuid}";
        $response = $this->getSigned($path)->assertOk();

        $this->assertSame([$second->id], array_column($response->json('data.messages'), 'id'));
    }

    public function test_messages_signature_accepts_reordered_query_parameters(): void
    {
        $site = $this->authenticatedSite();
        $visitor = Visitor::factory()->for($site)->create();
        $conversation = Conversation::factory()->for($site)->for($visitor)->create();
        $conversation->messages()->create(['role' => 'assistant', 'content' => 'hello']);

        $signed = "/api/v1/conversations/{$conversation->uuid}/messages?visitor_id={$visitor->uuid}&after_id=0";
        $sent = "/api/v1/conversations/{$conversation->uuid}/messages?after_id=0&visitor_id={$visitor->uuid}";

        $this->getSigned($sent, $this->signedFor('GET', $signed))
            ->assertOk()
            ->assertJsonPath('data.mode', ConversationMode::Ai->value);
    }

    public function test_two_different_reads_in_the_same_second_are_both_accepted(): void
    {
        $site = $this->authenticatedSite();
        $conversation = Conversation::factory()->for($site)->create();
        $timestamp = now()->timestamp;

        // Signatures are bound to the request, so concurrent polling of the list
        // and of a thread no longer collides in replay protection.
        $listPath = '/api/v1/conversations';
        $this->getSigned($listPath, $this->siteHeaders(
            extra: $this->signedHeaders('', timestamp: $timestamp, method: 'GET', path: $listPath)
        ))->assertOk();

        $threadPath = "/api/v1/conversations/{$conversation->uuid}";
        $this->getSigned($threadPath, $this->siteHeaders(
            extra: $this->signedHeaders('', timestamp: $timestamp, method: 'GET', path: $threadPath)
        ))->assertOk();
    }

    public function test_a_signature_cannot_be_replayed_against_a_different_endpoint(): void
    {
        $site = $this->authenticatedSite();
        $conversation = Conversation::factory()->for($site)->create();

        $headers = $this->signedFor('GET', '/api/v1/conversations');

        $this->getSigned("/api/v1/conversations/{$conversation->uuid}", $headers)
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'invalid_signature');
    }
}
