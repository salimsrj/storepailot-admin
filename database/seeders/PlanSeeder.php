<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Seed the CommercePilot subscription plans.
     */
    public function run(): void
    {
        foreach ($this->plans() as $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan,
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function plans(): array
    {
        return [
            [
                'name' => 'Free',
                'slug' => 'free',
                'monthly_messages' => 50,
                'max_sites' => 1,
                'monthly_price' => '0.00',
                'currency' => 'USD',
                'features' => [
                    'product_search' => true,
                    'recommendations' => true,
                    'cart' => true,
                    'checkout' => true,
                    'order_tracking' => false,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'monthly_messages' => 1000,
                'max_sites' => 3,
                'monthly_price' => '29.00',
                'currency' => 'USD',
                'features' => [
                    'product_search' => true,
                    'recommendations' => true,
                    'cart' => true,
                    'checkout' => true,
                    'order_tracking' => false,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'monthly_messages' => 5000,
                'max_sites' => 10,
                'monthly_price' => '79.00',
                'currency' => 'USD',
                'features' => [
                    'product_search' => true,
                    'recommendations' => true,
                    'cart' => true,
                    'checkout' => true,
                    'order_tracking' => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Agency',
                'slug' => 'agency',
                'monthly_messages' => 20000,
                'max_sites' => 50,
                'monthly_price' => '199.00',
                'currency' => 'USD',
                'features' => [
                    'product_search' => true,
                    'recommendations' => true,
                    'cart' => true,
                    'checkout' => true,
                    'order_tracking' => true,
                ],
                'is_active' => true,
            ],
        ];
    }
}
