<?php

namespace Tests\Feature;

use App\AI\Agent\PromptBuilder;
use App\Models\Site;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PromptBuilderTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_system_prompt_instructs_redirect_for_non_shopping_questions(): void
    {
        $site = Site::factory()->withSettings()->create();
        $settings = $site->settings()->firstOrFail();

        $prompt = app(PromptBuilder::class)->systemPrompt($site, $settings);

        $this->assertStringContainsString(
            'Stay strictly within shopping and store help',
            $prompt,
        );
        $this->assertStringContainsString(
            "I'm your shopping assistant, and I'm here to help you find the right products and make your shopping experience easier.",
            $prompt,
        );
        $this->assertStringContainsString(
            'do not answer the off-topic request',
            $prompt,
        );
    }

    public function test_system_prompt_includes_merchant_instructions_and_summary(): void
    {
        $site = Site::factory()->withSettings()->create();
        $settings = $site->settings()->firstOrFail();
        $settings->forceFill([
            'system_prompt' => 'Prefer eco-friendly products.',
        ])->save();

        $prompt = app(PromptBuilder::class)->systemPrompt(
            $site,
            $settings->fresh(),
            'Shopper is looking for running shoes.',
        );

        $this->assertStringContainsString('Prefer eco-friendly products.', $prompt);
        $this->assertStringContainsString('Shopper is looking for running shoes.', $prompt);
    }
}
