<?php

use App\Enums\ConversationMode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('mode', 16)->default(ConversationMode::Ai->value)->after('status');
            $table->timestamp('handover_at')->nullable()->after('mode');
            $table->string('handover_by')->nullable()->after('handover_at');

            $table->index(['site_id', 'mode']);
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['site_id', 'mode']);
            $table->dropColumn(['mode', 'handover_at', 'handover_by']);
        });
    }
};
