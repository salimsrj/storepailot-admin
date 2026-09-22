<?php

namespace Tests\Feature;

use App\AI\Agent\AgentContext;
use App\AI\Agent\CommerceAgent;
use App\AI\Contracts\AIProviderInterface;
use App\AI\DTOs\AIRequest;
use App\AI\DTOs\AIResponse;
use App\AI\DTOs\ToolCall;
use App\Exceptions\AIProviderException;
use App\Exceptions\InvalidToolArgumentsException;
use App\Models\Conversation;
use App\Models\Site;
use App\Services\WooCommerce\Contracts\WooCommerceClientInterface;
use App\Services\WooCommerce\DTOs\ProductData;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Mockery;
use Tests\TestCase;

class CommerceAgentTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_normal_response_is_stored_as_an_assistant_message(): void
    {
        $context = $this->context();
        $this->fakeAI()->push(new AIResponse('Hello shopper', [], 5, 3, 'fake-model', 'resp_1'));

        $result = app(CommerceAgent::class)->handle($context);

        $this->assertSame('Hello shopper', $result['message']->content);
        $this->assertSame(2, $context->conversation->messages()->count());
    }

    public function test_tool_call_is_executed_and_returned_to_the_model(): void
    {
        $context = $this->context();
        $this->fakeAI()
            ->push(new AIResponse(null, [
                new ToolCall('call_1', 'search_products', ['query' => 'black shoes']),
            ], 8, 4, 'fake-model'))
            ->push(new AIResponse('I found shoes.', [], 6, 4, 'fake-model', 'resp_2'));

        $this->app->instance(WooCommerceClientInterface::class, tap(
            Mockery::mock(WooCommerceClientInterface::class),
            function ($client): void {
                $client->shouldReceive('searchProducts')->once()->andReturn([
                    new ProductData(123, 'Nike Running Shoe', '89.00', 'USD', 'instock', null, 'https://example.com/p', 'Running shoe', ['Running'], true),
                ]);
            },
        ));

        $result = app(CommerceAgent::class)->handle($context);

        $this->assertSame('I found shoes.', $result['message']->content);
        $this->assertSame(123, $result['products'][0]['id']);
        $this->assertTrue($context->conversation->messages()->where('role', 'tool')->exists());
    }

    public function test_invalid_tool_arguments_are_rejected(): void
    {
        $context = $this->context();
        $this->fakeAI()->push(new AIResponse(null, [
            new ToolCall('call_1', 'search_products', ['query' => 'shoes', 'unknown' => true]),
        ], 1, 1, 'fake-model'));

        $this->expectException(InvalidToolArgumentsException::class);

        app(CommerceAgent::class)->handle($context);
    }

    public function test_provider_failure_is_normalized(): void
    {
        $this->fakeAI();
        $this->app->bind(AIProviderInterface::class, fn () => new class implements AIProviderInterface
        {
            public function name(): string
            {
                return 'fake';
            }

            public function complete(AIRequest $request): AIResponse
            {
                throw AIProviderException::unavailable();
            }

            public function stream(AIRequest $request): \Generator
            {
                yield from [];
            }

            public function model(): string
            {
                return 'fake-model';
            }
        });

        $this->expectException(AIProviderException::class);

        app(CommerceAgent::class)->handle($this->context());
    }

    public function test_maximum_tool_iterations_stops_safely(): void
    {
        $context = $this->context();
        $provider = $this->fakeAI();

        for ($i = 0; $i < 8; $i++) {
            $provider->push(new AIResponse(null, [
                new ToolCall('call_'.$i, 'search_products', ['query' => 'shoes']),
            ], 1, 1, 'fake-model'));
        }

        $this->app->instance(WooCommerceClientInterface::class, tap(
            Mockery::mock(WooCommerceClientInterface::class),
            function ($client): void {
                $client->shouldReceive('searchProducts')->andReturn([]);
            },
        ));

        $result = app(CommerceAgent::class)->handle($context);

        $this->assertSame('I could not complete that request right now.', $result['message']->content);
        $this->assertLessThanOrEqual(6, $context->conversation->messages()->where('role', 'tool')->count());
    }

    public function test_maximum_tool_iterations_falls_back_in_bangla_when_the_customer_wrote_bangla(): void
    {
        $context = $this->context('কালো জুতো দেখাও');
        $provider = $this->fakeAI();

        for ($i = 0; $i < 8; $i++) {
            $provider->push(new AIResponse(null, [
                new ToolCall('call_'.$i, 'search_products', ['query' => 'shoes']),
            ], 1, 1, 'fake-model'));
        }

        $this->app->instance(WooCommerceClientInterface::class, tap(
            Mockery::mock(WooCommerceClientInterface::class),
            function ($client): void {
                $client->shouldReceive('searchProducts')->andReturn([]);
            },
        ));

        $result = app(CommerceAgent::class)->handle($context);

        $this->assertSame('এই মুহূর্তে আপনার অনুরোধটি সম্পন্ন করতে পারিনি।', $result['message']->content);
    }

    public function test_follow_up_turn_replays_tool_calls_and_thought_signatures(): void
    {
        $context = $this->context();
        $provider = $this->fakeAI()
            ->push(new AIResponse(null, [
                new ToolCall('call_1', 'search_products', ['query' => 'black shoes'], 'sig_abc'),
            ], 8, 4, 'fake-model'))
            ->push(new AIResponse('I found shoes.', [], 6, 4, 'fake-model', 'resp_2'));

        $this->app->instance(WooCommerceClientInterface::class, tap(
            Mockery::mock(WooCommerceClientInterface::class),
            function ($client): void {
                $client->shouldReceive('searchProducts')->andReturn([
                    new ProductData(123, 'Nike Running Shoe', '89.00', 'USD', 'instock', null, 'https://example.com/p', 'Running shoe', ['Running'], true),
                ]);
            },
        ));

        app(CommerceAgent::class)->handle($context);

        $provider->push(new AIResponse('The Nike shoe is $89.', [], 4, 3, 'fake-model'));

        app(CommerceAgent::class)->handle(new AgentContext(
            site: $context->site,
            settings: $context->settings,
            visitor: $context->visitor,
            conversation: $context->conversation->fresh() ?? $context->conversation,
            message: 'Which one is cheaper?',
        ));

        $this->assertCount(3, $provider->requests);

        $replayed = collect($provider->requests[2]->messages)->first(
            fn (array $message): bool => ($message['role'] ?? null) === 'assistant'
                && isset($message['tool_calls'][0]),
        );

        $this->assertIsArray($replayed);
        $this->assertSame('call_1', $replayed['tool_calls'][0]['id']);
        $this->assertSame('search_products', $replayed['tool_calls'][0]['function']['name']);
        $this->assertSame('{"query":"black shoes"}', $replayed['tool_calls'][0]['function']['arguments']);
        $this->assertSame('sig_abc', $replayed['tool_calls'][0]['thought_signature']);
    }

    public function test_follow_up_turn_skips_incomplete_tool_history_from_a_failed_request(): void
    {
        $context = $this->context();
        $conversation = $context->conversation;

        $conversation->messages()->create([
            'role' => 'user',
            'content' => 'Find me black shoes.',
        ]);
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => '',
            'tool_name' => 'search_products',
            'tool_call_id' => 'call_stale',
        ]);
        $conversation->messages()->create([
            'role' => 'tool',
            'content' => '{"products":[]}',
            'tool_name' => 'search_products',
            'tool_call_id' => 'call_stale',
        ]);
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => 'I found shoes.',
        ]);

        $provider = $this->fakeAI()->push(new AIResponse('The Nike shoe is $89.', [], 4, 3, 'fake-model'));

        app(CommerceAgent::class)->handle(new AgentContext(
            site: $context->site,
            settings: $context->settings,
            visitor: $context->visitor,
            conversation: $conversation->fresh() ?? $conversation,
            message: 'Which one is cheaper?',
        ));

        $roles = array_column($provider->requests[0]->messages, 'role');

        $this->assertNotContains('tool', $roles);
        $this->assertSame('I found shoes.', collect($provider->requests[0]->messages)->first(
            fn (array $message): bool => ($message['role'] ?? null) === 'assistant',
        )['content'] ?? null);
    }

    private function context(string $message = 'Find me black shoes.'): AgentContext
    {
        $site = Site::factory()->withSettings()->create();
        $conversation = Conversation::factory()->for($site)->create();

        return new AgentContext(
            site: $site,
            settings: $site->settings()->firstOrFail(),
            visitor: $conversation->visitor()->firstOrFail(),
            conversation: $conversation,
            message: $message,
        );
    }
}
