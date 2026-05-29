<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retires the old belongsTo column. By this migration the pivot
 * (source_group_source) is the single source of truth for
 * source ↔ group membership. The backfill in the previous migration
 * runs first, so dropping the column here loses no data.
 *
 * Why this is so defensive:
 *
 *   - SQLite refuses to drop a column with an index still on it, so
 *     the explicit named index must come off first.
 *   - On MySQL, foreignId()->constrained() creates an FK constraint
 *     PLUS an implicit index. The explicit index we also added in
 *     the previous migration may or may not have been collapsed
 *     into the implicit one depending on driver version.
 *   - When this migration aborts halfway (as it did on the last two
 *     Forge deploys), the next deploy retries from a state where
 *     some of {named index, implicit index, FK} are already gone.
 *
 * Each of the three drops (named index, FK constraint, column) is
 * therefore independent and try/catch-wrapped: missing-target errors
 * become no-ops instead of deploy failures. The terminal dropColumn
 * is the only step that MUST succeed — guarded by the hasColumn()
 * check at the top.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sources', 'source_group_id')) {
            return;
        }

        // Drop the explicit named index if it exists. SQLite needs
        // this gone before the column drop; MySQL is fine either way.
        try {
            Schema::table('sources', function (Blueprint $table) {
                $table->dropIndex('sources_source_group_id_index');
            });
        } catch (\Throwable $e) {
            // Index either doesn't exist under that name (MySQL may
            // have collapsed it into the FK-implicit one) or was
            // dropped by an earlier partial deploy. Either way,
            // nothing more to do here.
        }

        // Drop the FK constraint if it exists. SQLite doesn't enforce
        // FKs at the schema level so this is a no-op there; MySQL
        // raises 1091 ("Can't DROP") when the named constraint is
        // already gone.
        try {
            Schema::table('sources', function (Blueprint $table) {
                $table->dropForeign(['source_group_id']);
            });
        } catch (\Throwable $e) {
            // FK already removed by an earlier partial deploy or never
            // present on this driver. Continue to the column drop.
        }

        // Finally drop the column itself. If we got here, hasColumn
        // returned true at the top, so this is the one step that
        // must succeed — let any error bubble up.
        Schema::table('sources', function (Blueprint $table) {
            $table->dropColumn('source_group_id');
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
