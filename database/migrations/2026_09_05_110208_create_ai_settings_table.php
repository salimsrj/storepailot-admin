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
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->text('openai_api_key')->nullable();
            $table->string('openai_organization')->nullable();
            $table->string('model', 80);
            $table->unsignedSmallInteger('timeout');
            $table->unsignedTinyInteger('max_tool_iterations');
            $table->unsignedTinyInteger('max_context_messages');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
