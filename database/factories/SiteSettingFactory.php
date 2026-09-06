<?php

namespace Database\Factories;

use App\Models\Site;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'assistant_name' => 'CommercePilot',
            'welcome_message' => 'Hi! How can I help you find the right product today?',
            'language' => 'en',
            'tone' => 'helpful',
            'system_prompt' => null,
            'enable_product_search' => true,
            'enable_recommendations' => true,
            'enable_cart' => true,
            'enable_checkout' => true,
            'enable_order_tracking' => false,
            'settings' => [
                'currency' => 'USD',
            ],
        ];
    }
}
