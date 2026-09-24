<?php

namespace Tests\Feature;

use App\AI\DTOs\AIResponse;
use App\Models\Conversation;
use App\Models\UsagePeriod;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ChatApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_chat_returns_an_assistant_message_and_usage(): void
    {
        $this->enableAgentMode($this->authenticatedSite());
        $this->fakeAI();
        $visitorId = '11111111-1111-1111-1111-111111111111';

        $response = $this->postJson('/api/v1/chat', [
            'visitor_id' => $visitorId,
            'message' => 'Show me black running shoes',
        ], $this->siteHeaders());

        $response->assertOk()
            ->assertJsonPath('data.message.role', 'assistant')
            ->assertJsonPath('data.message.content', 'I found these running shoes for you.')
            ->assertJsonPath('data.usage.used', 1)
            ->assertJsonPath('data.usage.limit', 50)
            ->assertJsonPath('data.usage.remaining', 49);

        $this->assertNotEmpty($response->json('data.conversation_id'));
        $this->assertDatabaseHas('messages', [
            'role' => 'user',
            'content' => 'Show me black running shoes',
        ]);
    }

    public function test_chat_rejects_a_conversation_from_another_site(): void
    {
        $this->enableAgentMode($this->authenticatedSite());
        $foreign = Conversation::factory()->create();
        $this->fakeAI();

        $this->postJson('/api/v1/chat', [
            'conversation_id' => $foreign->uuid,
            'visitor_id' => '11111111-1111-1111-1111-111111111111',
            'message' => 'Hello',
        ], $this->siteHeaders())
            ->assertNotFound()
            ->assertJsonPath('error.code', 'conversation_not_found');
    }

    public function test_chat_rejects_a_conversation_owned_by_another_visitor(): void
    {
        $site = $this->enableAgentMode($this->authenticatedSite());
        $visitor = Visitor::factory()->for($site)->create();
        $other = Visitor::factory()->for($site)->create();
        $conversation = Conversation::factory()->for($site)->for($visitor)->create();
        $this->fakeAI();

        $this->postJson('/api/v1/chat', [
            'conversation_id' => $conversation->uuid,
            'visitor_id' => $other->uuid,
            'message' => 'Hello',
        ], $this->siteHeaders())
            ->assertForbidden()
            ->assertJsonPath('error.code', 'conversation_forbidden');
    }

    public function test_usage_limit_returns_402_without_calling_the_ai_provider(): void
    {
        $site = $this->enableAgentMode($this->authenticatedSite());
        UsagePeriod::factory()->for($site)->exhausted()->create();
        $provider = $this->fakeAI();
        $provider->push(new AIResponse('should not be used', [], 1, 1, 'fake-model'));

        $this->postJson('/api/v1/chat', [
            'visitor_id' => '11111111-1111-1111-1111-111111111111',
            'message' => 'Show me black running shoes',
        ], $this->siteHeaders())
            ->assertStatus(402)
            ->assertJsonPath('error.code', 'usage_limit_reached')
            ->assertJsonPath('error.upgrade_required', true);

        $this->assertCount(1, $provider->queue);
        $this->assertDatabaseMissing('messages', ['content' => 'Show me black running shoes']);
    }

    #[DataProvider('greetingMessages')]
    public function test_greeting_returns_a_ready_reply_without_calling_ai(string $message): void
    {
        $this->enableAgentMode($this->authenticatedSite());
        $provider = $this->fakeAI();
        $provider->push(new AIResponse('should not be used', [], 1, 1, 'fake-model'));

        $response = $this->postJson('/api/v1/chat', [
            'visitor_id' => '11111111-1111-1111-1111-111111111111',
            'message' => $message,
        ], $this->siteHeaders());

        $response->assertOk()
            ->assertJsonPath('data.message.role', 'assistant')
            ->assertJsonPath('data.message.content', 'Hi! How can I help you find the right product today?')
            ->assertJsonPath('data.usage.used', 0)
            ->assertJsonPath('data.usage.limit', 50)
            ->assertJsonPath('data.usage.remaining', 50);

        $this->assertCount(1, $provider->queue);
        $this->assertDatabaseHas('messages', [
            'role' => 'user',
            'content' => $message,
        ]);
        $this->assertDatabaseHas('messages', [
            'role' => 'assistant',
            'content' => 'Hi! How can I help you find the right product today?',
        ]);
    }

    /**
     * @return list<list<string>>
     */
    public static function greetingMessages(): array
    {
        return [
            ['hi'],
            ['hello'],
            ['how are you'],
        ];
    }

    #[DataProvider('multilingualShoppingMessages')]
    public function test_multilingual_shopping_question_reaches_the_ai_and_stores_the_message(string $message): void
    {
        $this->enableAgentMode($this->authenticatedSite());
        $this->fakeAI();

        $response = $this->postJson('/api/v1/chat', [
            'visitor_id' => '11111111-1111-1111-1111-111111111111',
            'message' => $message,
        ], $this->siteHeaders());

        $response->assertOk()
            ->assertJsonPath('data.message.role', 'assistant')
            ->assertJsonPath('data.message.content', 'I found these running shoes for you.')
            ->assertJsonPath('data.usage.used', 1)
            ->assertJsonPath('data.usage.remaining', 49);

        $this->assertDatabaseHas('messages', [
            'role' => 'user',
            'content' => $message,
        ]);
    }

    /**
     * @return array<string, list{string}>
     */
    public static function multilingualShoppingMessages(): array
    {
        return [
            'bangla' => ['কালো জুতো দেখাও'],
            'hindi' => ['काले जूते दिखाओ'],
            'banglish' => ['kalo juta dekhaw'],
        ];
    }

    public function test_shopping_question_that_starts_with_a_greeting_still_calls_the_ai(): void
    {
        $this->enableAgentMode($this->authenticatedSite());
        $this->fakeAI();

        $response = $this->postJson('/api/v1/chat', [
            'visitor_id' => '11111111-1111-1111-1111-111111111111',
            'message' => 'hi, show me black running shoes',
        ], $this->siteHeaders());

        $response->assertOk()
            ->assertJsonPath('data.message.role', 'assistant')
            ->assertJsonPath('data.message.content', 'I found these running shoes for you.')
            ->assertJsonPath('data.usage.used', 1)
            ->assertJsonPath('data.usage.remaining', 49);

        $this->assertDatabaseHas('messages', [
            'role' => 'user',
            'content' => 'hi, show me black running shoes',
        ]);
    }

    public function test_chat_validates_the_message(): void
    {
        $this->authenticatedSite();

        $this->postJson('/api/v1/chat', [
            'visitor_id' => 'not-a-uuid',
            'message' => '',
        ], $this->siteHeaders())
            ->assertUnprocessable();
    }

    public function test_request_id_is_returned_on_api_responses(): void
    {
        $this->authenticatedSite();

        $this->getJson('/api/v1/site', $this->siteHeaders() + [
            'X-Request-ID' => 'req-123',
        ])->assertOk()->assertHeader('X-Request-ID', 'req-123');
    }
}
