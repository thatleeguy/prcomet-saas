<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Promote sources ↔ catalogues from belongsTo to many-to-many.
 *
 * A publication can be relevant to several catalogues — "Crux
 * Investor" sits in both Mining and Clean Energy without
 * duplicating the row, the feed, or the ingestion run. Ingestion
 * stays single-stream per source (one IngestSourceJob, one
 * PublicationItem feed); the pivot only affects Source::visibleTo
 * (which joins through it to compute the team-visible corpus).
 *
 * Migration sequence:
 *   1. Create the pivot.
 *   2. Backfill it from sources.source_group_id (everything currently
 *      pinned to one group becomes a single pivot row).
 *   3. Drop sources.source_group_id in the follow-up migration so
 *      there's no longer two sources of truth.
 *
 * The pivot itself carries no provenance — it's a pure category tag.
 * Subscriptions (team → group) and provenance (who granted what)
 * stay on source_group_subscriptions.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('source_group_source')) {
            return;
        }

        Schema::create('source_group_source', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_group_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['source_id', 'source_group_id'], 'sgs_unique_source_group');
            $table->index('source_group_id');
        });

        // Backfill the new pivot from the old belongsTo column. Idempotent
        // against repeat runs because of the unique constraint above.
        if (Schema::hasColumn('sources', 'source_group_id')) {
            $rows = DB::table('sources')
                ->whereNotNull('source_group_id')
                ->select('id', 'source_group_id')
                ->get();

            foreach ($rows as $row) {
                DB::table('source_group_source')->insertOrIgnore([
                    'source_id' => $row->id,
                    'source_group_id' => $row->source_group_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('source_group_source');
    }
};
