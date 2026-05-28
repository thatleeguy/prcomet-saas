<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inbox seen/unseen state for watch hits.
 *
 * Modelled team-wide rather than per-user: the typical PR team is 1–3
 * people and the workflow is "we've looked at this" / "we haven't yet",
 * not personal Gmail-style read tracking. Easy to migrate to a pivot
 * table later if multi-user precision matters.
 *
 * Indexed by (watch_id, seen_at) so the "unread first" query — `WHERE
 * seen_at IS NULL ORDER BY matched_at DESC` — stays cheap as hits
 * accumulate.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('watch_hits', 'seen_at')) {
            return;
        }

        Schema::table('watch_hits', function (Blueprint $table) {
            $table->timestamp('seen_at')->nullable()->after('llm_reasoning');
            $table->index(['watch_id', 'seen_at']);
        });
    }

    public function down(): void
    {
        Schema::table('watch_hits', function (Blueprint $table) {
            $table->dropIndex(['watch_id', 'seen_at']);
            $table->dropColumn('seen_at');
        });
    }
};
