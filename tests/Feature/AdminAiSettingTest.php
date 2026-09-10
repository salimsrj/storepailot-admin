<?php

namespace Tests\Feature;

use App\AI\Contracts\AIProviderInterface;
use App\AI\DTOs\AIRequest;
use App\AI\Providers\GeminiProvider;
use App\AI\Providers\OpenAIProvider;
use App\Enums\AiProvider;
use App\Exceptions\AIProviderException;
use App\Models\AiSetting;
use App\Models\User;
use App\Services\Ai\AiSettingsService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminAiSettingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_cannot_view_ai_settings(): void
    {
        $this->get(route('admin.settings.ai'))->assertRedirect(route('admin.login'));
    }

    public function test_non_admins_cannot_view_ai_settings(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.settings.ai'))
            ->assertForbidden();
    }

    public function test_admin_can_view_and_update_ai_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.settings.ai'))
            ->assertOk()
            ->assertSee('OpenAI')
            ->assertSee('Gemini')
            ->assertSee('Active provider')
            ->assertSee('Greeting replies');

        $this->actingAs($admin)
            ->put(route('admin.settings.ai.update'), [
                'provider' => AiProvider::OpenAi->value,
                'openai_api_key' => 'sk-dashboard-secret-key',
                'openai_organization' => 'org-test',
                'model' => 'gpt-4o-mini',
                'timeout' => 45,
                'max_tool_iterations' => 4,
                'max_context_messages' => 16,
                'greeting_phrases' => "hi\nhello\nwelcome",
                'greeting_reply' => 'Welcome! How can I help?',
            ])
            ->assertRedirect(route('admin.settings.ai'));

        $settings = app(AiSettingsService::class)->current();

        $this->assertSame(AiProvider::OpenAi, $settings->provider);
        $this->assertSame('sk-dashboard-secret-key', $settings->openai_api_key);
        $this->assertSame('gpt-4o-mini', $settings->model);
        $this->assertSame(45, $settings->timeout);
        $this->assertSame('••••-key', $settings->maskedApiKey());
        $this->assertSame(['hi', 'hello', 'welcome'], $settings->greeting_phrases);
        $this->assertSame('Welcome! How can I help?', $settings->greeting_reply);
    }

    public function test_admin_can_switch_to_gemini_and_save_a_key(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.ai.update'), [
                'provider' => AiProvider::Gemini->value,
                'gemini_api_key' => 'AIza-dashboard-secret',
                'model' => 'gemini-3.8-flash',
                'timeout' => 30,
                'max_tool_iterations' => 5,
                'max_context_messages' => 20,
            ])
            ->assertRedirect(route('admin.settings.ai'));

        $settings = app(AiSettingsService::class)->current();

        $this->assertSame(AiProvider::Gemini, $settings->provider);
        $this->assertSame('AIza-dashboard-secret', $settings->gemini_api_key);
        $this->assertSame('gemini-3.8-flash', $settings->model);
        $this->assertSame('••••cret', $settings->maskedGeminiApiKey());
        $this->assertInstanceOf(GeminiProvider::class, app(AIProviderInterface::class));
    }

    public function test_blank_api_key_keeps_the_existing_key(): void
    {
        AiSetting::factory()->withApiKey('sk-keep-this-key')->create([
            'model' => 'gpt-4.1-mini',
        ]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.ai.update'), [
                'provider' => AiProvider::OpenAi->value,
                'openai_api_key' => '',
                'model' => 'gpt-4o-mini',
                'timeout' => 30,
                'max_tool_iterations' => 5,
                'max_context_messages' => 20,
            ])
            ->assertRedirect(route('admin.settings.ai'));

        $this->assertSame('sk-keep-this-key', app(AiSettingsService::class)->apiKey());
        $this->assertSame('gpt-4o-mini', app(AiSettingsService::class)->model());
    }

    public function test_blank_gemini_api_key_keeps_the_existing_key(): void
    {
        AiSetting::factory()->gemini('AIza-keep-this-key')->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.ai.update'), [
                'provider' => AiProvider::Gemini->value,
                'gemini_api_key' => '',
                'model' => 'gemini-3.8-flash',
                'timeout' => 30,
                'max_tool_iterations' => 5,
                'max_context_messages' => 20,
            ])
            ->assertRedirect(route('admin.settings.ai'));

        $this->assertSame('AIza-keep-this-key', app(AiSettingsService::class)->geminiApiKey());
        $this->assertSame(AiProvider::Gemini, app(AiSettingsService::class)->provider());
    }

    public function test_openai_provider_uses_the_dashboard_api_key(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl_test',
                'model' => 'gpt-4o-mini',
                'choices' => [[
                    'message' => ['content' => 'Hello from OpenAI'],
                ]],
                'usage' => ['prompt_tokens' => 3, 'completion_tokens' => 2],
            ]),
        ]);

        AiSetting::factory()->withApiKey('sk-from-dashboard')->create([
            'model' => 'gpt-4o-mini',
        ]);

        $response = app(OpenAIProvider::class)->complete(new AIRequest([
            ['role' => 'user', 'content' => 'Hi'],
        ]));

        $this->assertSame('Hello from OpenAI', $response->content);
        $this->assertSame('openai', app(OpenAIProvider::class)->name());

        Http::assertSent(function ($request): bool {
            return $request->hasHeader('Authorization', 'Bearer sk-from-dashboard')
                && $request['model'] === 'gpt-4o-mini';
        });
    }

    public function test_gemini_provider_uses_the_dashboard_api_key_and_maps_tool_calls(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/*' => Http::response([
                'responseId' => 'resp_gemini',
                'modelVersion' => 'gemini-3.8-flash',
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'functionCall' => [
                                'name' => 'search_products',
                                'args' => ['query' => 'shoes'],
                            ],
                            'thoughtSignature' => 'sig_abc123',
                        ]],
                    ],
                ]],
                'usageMetadata' => [
                    'promptTokenCount' => 8,
                    'candidatesTokenCount' => 4,
                ],
            ]),
        ]);

        AiSetting::factory()->gemini('AIza-from-dashboard')->create();

        $response = app(GeminiProvider::class)->complete(new AIRequest(
            messages: [
                ['role' => 'system', 'content' => 'You are CommercePilot.'],
                ['role' => 'user', 'content' => 'Find running shoes'],
            ],
            tools: [[
                'type' => 'function',
                'function' => [
                    'name' => 'search_products',
                    'description' => 'Search the catalog',
                    'parameters' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'query' => ['type' => 'string'],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ]],
        ));

        $this->assertNull($response->content);
        $this->assertTrue($response->hasToolCalls());
        $this->assertSame('search_products', $response->toolCalls[0]->name);
        $this->assertSame(['query' => 'shoes'], $response->toolCalls[0]->arguments);
        $this->assertSame('sig_abc123', $response->toolCalls[0]->thoughtSignature);
        $this->assertSame(8, $response->inputTokens);
        $this->assertSame('gemini', app(GeminiProvider::class)->name());
        $this->assertInstanceOf(GeminiProvider::class, app(AIProviderInterface::class));

        Http::assertSent(function ($request): bool {
            $body = $request->data();

            return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key=AIza-from-dashboard'
                && $request->hasHeader('x-goog-api-key', 'AIza-from-dashboard')
                && ($body['systemInstruction']['parts'][0]['text'] ?? null) === 'You are CommercePilot.'
                && ($body['contents'][0]['role'] ?? null) === 'user'
                && ($body['tools'][0]['functionDeclarations'][0]['name'] ?? null) === 'search_products'
                && ! array_key_exists('additionalProperties', $body['tools'][0]['functionDeclarations'][0]['parameters'] ?? []);
        });
    }

    public function test_gemini_provider_sends_function_responses_for_tool_results(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [['text' => 'Here are some shoes.']],
                    ],
                ]],
                'usageMetadata' => [
                    'promptTokenCount' => 12,
                    'candidatesTokenCount' => 6,
                ],
            ]),
        ]);

        AiSetting::factory()->gemini('AIza-from-dashboard')->create();

        $response = app(GeminiProvider::class)->complete(new AIRequest([
            ['role' => 'user', 'content' => 'Find shoes'],
            [
                'role' => 'assistant',
                'content' => null,
                'tool_calls' => [[
                    'id' => 'call_1',
                    'type' => 'function',
                    'thought_signature' => 'sig_from_model',
                    'function' => [
                        'name' => 'search_products',
                        'arguments' => '{"query":"shoes"}',
                    ],
                ]],
            ],
            [
                'role' => 'tool',
                'tool_call_id' => 'call_1',
                'content' => '{"products":[]}',
            ],
        ]));

        $this->assertSame('Here are some shoes.', $response->content);

        Http::assertSent(function ($request): bool {
            $body = $request->data();
            $contents = $body['contents'] ?? [];

            return ($contents[1]['role'] ?? null) === 'model'
                && ($contents[1]['parts'][0]['functionCall']['name'] ?? null) === 'search_products'
                && ($contents[1]['parts'][0]['thoughtSignature'] ?? null) === 'sig_from_model'
                && ($contents[2]['role'] ?? null) === 'user'
                && ($contents[2]['parts'][0]['functionResponse']['name'] ?? null) === 'search_products'
                && ($contents[2]['parts'][0]['functionResponse']['response']['products'] ?? null) === [];
        });
    }

    public function test_switching_back_to_openai_uses_the_openai_key(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl_switched',
                'model' => 'gpt-4o-mini',
                'choices' => [[
                    'message' => ['content' => 'Back on OpenAI'],
                ]],
                'usage' => ['prompt_tokens' => 2, 'completion_tokens' => 2],
            ]),
        ]);

        AiSetting::factory()->gemini('AIza-from-dashboard')->withApiKey('sk-from-dashboard')->create([
            'model' => 'gpt-4o-mini',
        ]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.ai.update'), [
                'provider' => AiProvider::OpenAi->value,
                'model' => 'gpt-4o-mini',
                'timeout' => 30,
                'max_tool_iterations' => 5,
                'max_context_messages' => 20,
            ])
            ->assertRedirect(route('admin.settings.ai'));

        $this->assertSame(AiProvider::OpenAi, app(AiSettingsService::class)->provider());
        $this->assertInstanceOf(OpenAIProvider::class, app(AIProviderInterface::class));

        $response = app(OpenAIProvider::class)->complete(new AIRequest([
            ['role' => 'user', 'content' => 'Hi'],
        ]));

        $this->assertSame('Back on OpenAI', $response->content);

        Http::assertSent(function ($request): bool {
            return $request->hasHeader('Authorization', 'Bearer sk-from-dashboard');
        });
    }

    public function test_switching_to_gemini_with_an_openai_model_stores_the_gemini_default(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.ai.update'), [
                'provider' => AiProvider::Gemini->value,
                'gemini_api_key' => 'AIza-dashboard-secret',
                'model' => 'gpt-4o-mini',
                'timeout' => 30,
                'max_tool_iterations' => 5,
                'max_context_messages' => 20,
            ])
            ->assertRedirect(route('admin.settings.ai'));

        $this->assertSame(AiProvider::Gemini, app(AiSettingsService::class)->provider());
        $this->assertSame('gemini-3.8-flash', app(AiSettingsService::class)->model());
    }

    public function test_gemini_provider_remaps_a_stored_openai_model_before_calling_google(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [['text' => 'Hello from Gemini']],
                    ],
                ]],
                'usageMetadata' => [
                    'promptTokenCount' => 3,
                    'candidatesTokenCount' => 2,
                ],
            ]),
        ]);

        AiSetting::factory()->gemini('AIza-from-dashboard')->create([
            'model' => 'gpt-4o-mini',
        ]);

        $response = app(GeminiProvider::class)->complete(new AIRequest([
            ['role' => 'user', 'content' => 'Hi'],
        ]));

        $this->assertSame('Hello from Gemini', $response->content);
        $this->assertSame('gemini-3.8-flash', app(AiSettingsService::class)->model());

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key=AIza-from-dashboard';
        });
    }

    public function test_retired_gemini_flash_is_remapped_to_the_current_default(): void
    {
        $service = app(AiSettingsService::class);

        $this->assertSame('gemini-3.8-flash', $service->modelFor(AiProvider::Gemini, 'gemini-2.5-flash'));
        $this->assertSame('gemini-3.8-flash', $service->modelFor(AiProvider::Gemini, 'gemini-3.6-flash'));
    }

    public function test_invalid_gemini_api_key_returns_a_clear_error(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/*' => Http::response([
                'error' => [
                    'code' => 400,
                    'message' => 'API key not valid. Please pass a valid API key.',
                    'status' => 'INVALID_ARGUMENT',
                ],
            ], 400),
        ]);

        AiSetting::factory()->gemini('AIza-invalid-key')->create();

        try {
            app(GeminiProvider::class)->complete(new AIRequest([
                ['role' => 'user', 'content' => 'Hi'],
            ]));
            $this->fail('Expected an invalid API key exception.');
        } catch (AIProviderException $exception) {
            $this->assertSame('ai_invalid_api_key', $exception->errorCode);
            $this->assertSame(401, $exception->status);
        }
    }

    public function test_gemini_not_found_model_returns_a_clear_error(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/*' => Http::response([
                'error' => [
                    'code' => 404,
                    'message' => 'models/not-a-model is not found',
                    'status' => 'NOT_FOUND',
                ],
            ], 404),
        ]);

        AiSetting::factory()->gemini('AIza-from-dashboard')->create([
            'model' => 'not-a-model',
        ]);

        try {
            app(GeminiProvider::class)->complete(new AIRequest([
                ['role' => 'user', 'content' => 'Hi'],
            ]));
            $this->fail('Expected an invalid model exception.');
        } catch (AIProviderException $exception) {
            $this->assertSame('ai_invalid_model', $exception->errorCode);
            $this->assertSame(422, $exception->status);
        }
    }

    public function test_current_settings_ignore_a_serialized_cache_entry(): void
    {
        Cache::put('ai-settings', (object) ['broken' => true]);

        $settings = app(AiSettingsService::class)->current();

        $this->assertInstanceOf(AiSetting::class, $settings);
        $this->assertFalse(Cache::has('ai-settings'));
    }

    public function test_validation_rejects_an_invalid_timeout(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.settings.ai'))
            ->put(route('admin.settings.ai.update'), [
                'provider' => AiProvider::OpenAi->value,
                'model' => 'gpt-4.1-mini',
                'timeout' => 1,
                'max_tool_iterations' => 5,
                'max_context_messages' => 20,
            ])
            ->assertRedirect(route('admin.settings.ai'))
            ->assertSessionHasErrors('timeout');
    }
}
