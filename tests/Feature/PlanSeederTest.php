<?php

namespace Tests\Feature;

use App\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PlanSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seeds_the_four_commercepilot_plans_with_database_limits(): void
    {
        $this->seed(PlanSeeder::class);

        $this->assertSame(4, Plan::query()->count());

        $this->assertDatabaseHas('plans', [
            'slug' => 'free',
            'monthly_messages' => 50,
            'max_sites' => 1,
            'monthly_price' => 0.00,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('plans', [
            'slug' => 'starter',
            'monthly_messages' => 1000,
            'max_sites' => 3,
        ]);

        $this->assertDatabaseHas('plans', [
            'slug' => 'business',
            'monthly_messages' => 5000,
            'max_sites' => 10,
        ]);

        $this->assertDatabaseHas('plans', [
            'slug' => 'agency',
            'monthly_messages' => 20000,
            'max_sites' => 50,
        ]);
    }

    public function test_plan_seeder_is_idempotent(): void
    {
        $this->seed(PlanSeeder::class);
        $this->seed(PlanSeeder::class);

        $this->assertSame(4, Plan::query()->count());
        $this->assertSame(1, Plan::query()->where('slug', 'free')->count());
    }
}
