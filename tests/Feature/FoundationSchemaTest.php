<?php

namespace Tests\Feature;

use App\Enums\PaymentProvider;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\UsagePeriod;
use App\Models\User;
use App\Models\WebhookEvent;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FoundationSchemaTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_foundation_tables_exist(): void
    {
        foreach ([
            'users',
            'plans',
            'subscriptions',
            'sites',
            'site_settings',
            'visitors',
            'conversations',
            'messages',
            'usage_periods',
            'usage_events',
            'webhook_events',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}].");
        }
    }

    public function test_users_table_includes_uuid_and_status(): void
    {
        $this->assertTrue(Schema::hasColumns('users', ['uuid', 'status']));
    }

    public function test_messages_table_does_not_have_updated_at(): void
    {
        $this->assertTrue(Schema::hasColumn('messages', 'created_at'));
        $this->assertFalse(Schema::hasColumn('messages', 'updated_at'));
    }

    public function test_usage_periods_reject_duplicate_site_and_period_start(): void
    {
        $site = Site::factory()->create();
        UsagePeriod::factory()->for($site)->create([
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
        ]);

        $this->expectException(QueryException::class);

        UsagePeriod::factory()->for($site)->create([
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
        ]);
    }

    public function test_webhook_events_reject_duplicate_provider_event_id(): void
    {
        WebhookEvent::factory()->create([
            'provider' => PaymentProvider::Stripe,
            'event_id' => 'evt_duplicate_1',
        ]);

        $this->expectException(QueryException::class);

        WebhookEvent::factory()->create([
            'provider' => PaymentProvider::Stripe,
            'event_id' => 'evt_duplicate_1',
        ]);
    }

    public function test_site_settings_reject_a_second_row_for_the_same_site(): void
    {
        $site = Site::factory()->withSettings()->create();

        $this->assertSame(1, $site->settings()->count());

        $this->expectException(QueryException::class);

        SiteSetting::factory()->for($site)->create();
    }

    public function test_user_uuid_is_unique(): void
    {
        $user = User::factory()->create();

        $this->expectException(QueryException::class);

        User::factory()->make([
            'email' => 'duplicate-uuid@example.com',
        ])->forceFill([
            'uuid' => $user->uuid,
        ])->save();
    }
}
