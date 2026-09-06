<?php

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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('assistant_name')->default('CommercePilot');
            $table->text('welcome_message')->nullable();
            $table->string('language', 16)->default('en');
            $table->string('tone', 64)->default('helpful');
            $table->text('system_prompt')->nullable();
            $table->boolean('enable_product_search')->default(true);
            $table->boolean('enable_recommendations')->default(true);
            $table->boolean('enable_cart')->default(true);
            $table->boolean('enable_checkout')->default(true);
            $table->boolean('enable_order_tracking')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
