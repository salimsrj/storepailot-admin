<?php

use App\Enums\AiProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->string('provider', 16)->default(AiProvider::OpenAi->value)->after('id');
            $table->text('gemini_api_key')->nullable()->after('openai_organization');
        });
    }

    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->dropColumn(['provider', 'gemini_api_key']);
        });
    }
};
