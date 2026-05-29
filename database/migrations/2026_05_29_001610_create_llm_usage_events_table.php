<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-call audit log of every LLM request, used for two things:
 *  - Cost ledger: feeds the daily/weekly hard-cap budget enforcement.
 *  - Per-team usage tracking: feeds the soft-alert "team X has spent $Y
 *    today" thresholds the operators get emailed about.
 *
 * cost_cents is denormalised on write so budget queries are pure
 * arithmetic (SUM, no per-row pricing lookups). Pricing lives in
 * config/llm.php and is applied by LlmUsageTracker at insert time.
 *
 * team_id / user_id are nullable because some LLM features run from
 * jobs that have no auth context. When that's the case the cost
 * still counts toward the system-wide cap.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('llm_usage_events')) {
            return;
        }

        Schema::create('llm_usage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('feature'); // match_brief | watch_hit_confirm | ...
            $table->string('model');
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('cost_cents')->default(0);
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['team_id', 'created_at']);
            $table->index(['feature', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llm_usage_events');
    }
};
