<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retires the old belongsTo column. By this migration the pivot
 * (source_group_source) is the single source of truth for
 * source ↔ group membership. Running this without first having run
 * the backfill in the previous migration would lose data, so we
 * guard with hasColumn() and skip cleanly if it's already gone.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sources', 'source_group_id')) {
            return;
        }

        // SQLite refuses to drop a column that still has an index on it,
        // so the index goes first.
        Schema::table('sources', function (Blueprint $table) {
            $table->dropIndex('sources_source_group_id_index');
        });

        Schema::table('sources', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->foreignId('source_group_id')->nullable()
                ->constrained('source_groups')->nullOnDelete();
        });
    }
};
