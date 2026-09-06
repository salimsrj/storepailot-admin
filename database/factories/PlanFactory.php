<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'monthly_messages' => 50,
            'max_sites' => 1,
            'monthly_price' => '0.00',
            'currency' => 'USD',
            'features' => [
                'product_search' => true,
                'recommendations' => true,
                'cart' => false,
                'checkout' => false,
                'order_tracking' => false,
            ],
            'is_active' => true,
        ];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Free',
            'slug' => 'free',
            'monthly_messages' => 50,
            'max_sites' => 1,
            'monthly_price' => '0.00',
        ]);
    }

    public function starter(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Starter',
            'slug' => 'starter',
            'monthly_messages' => 1000,
            'max_sites' => 3,
            'monthly_price' => '29.00',
            'features' => [
                'product_search' => true,
                'recommendations' => true,
                'cart' => true,
                'checkout' => true,
                'order_tracking' => false,
            ],
        ]);
    }

    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Business',
            'slug' => 'business',
            'monthly_messages' => 5000,
            'max_sites' => 10,
            'monthly_price' => '79.00',
            'features' => [
                'product_search' => true,
                'recommendations' => true,
                'cart' => true,
                'checkout' => true,
                'order_tracking' => true,
            ],
        ]);
    }

    public function agency(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Agency',
            'slug' => 'agency',
            'monthly_messages' => 20000,
            'max_sites' => 50,
            'monthly_price' => '199.00',
            'features' => [
                'product_search' => true,
                'recommendations' => true,
                'cart' => true,
                'checkout' => true,
                'order_tracking' => true,
            ],
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
