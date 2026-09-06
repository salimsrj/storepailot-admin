<?php

namespace Tests\Feature;

use App\AI\DTOs\AIRequest;
use App\AI\Providers\OpenAIProvider;
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
            ->assertSee('Greeting replies');

        $this->actingAs($admin)
            ->put(route('admin.settings.ai.update'), [
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

        $this->assertSame('sk-dashboard-secret-key', $settings->openai_api_key);
        $this->assertSame('gpt-4o-mini', $settings->model);
        $this->assertSame(45, $settings->timeout);
        $this->assertSame('••••-key', $settings->maskedApiKey());
        $this->assertSame(['hi', 'hello', 'welcome'], $settings->greeting_phrases);
        $this->assertSame('Welcome! How can I help?', $settings->greeting_reply);
    }

    public function test_blank_api_key_keeps_the_existing_key(): void
    {
        AiSetting::factory()->withApiKey('sk-keep-this-key')->create([
            'model' => 'gpt-4.1-mini',
        ]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.ai.update'), [
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

        Http::assertSent(function ($request): bool {
            return $request->hasHeader('Authorization', 'Bearer sk-from-dashboard')
                && $request['model'] === 'gpt-4o-mini';
        });
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
                'model' => 'gpt-4.1-mini',
                'timeout' => 1,
                'max_tool_iterations' => 5,
                'max_context_messages' => 20,
            ])
            ->assertRedirect(route('admin.settings.ai'))
            ->assertSessionHasErrors('timeout');
    }
}
