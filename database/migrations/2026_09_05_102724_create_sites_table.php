<?php

use App\Enums\SiteStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('url');
            $table->string('domain');
            $table->char('site_token_hash', 64)->unique();
            $table->text('site_secret_encrypted');
            $table->string('plugin_version')->nullable();
            $table->string('wordpress_version')->nullable();
            $table->string('woocommerce_version')->nullable();
            $table->string('status', 32)->default(SiteStatus::Pending->value);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('domain');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
