<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Retires the old belongsTo column. By this migration the pivot
 * (source_group_source) is the single source of truth for
 * source ↔ group membership. Running this without first having run
 * the backfill in the previous migration would lose data, so we
 * guard with hasColumn() and skip cleanly if it's already gone.
 *
 * Driver quirks handled:
 *   - SQLite refuses to drop a column that still has an index on it,
 *     so the explicit index must come off first.
 *   - On MySQL, foreignId()->constrained() creates an implicit
 *     FK-backed index that may share or differ from the explicit
 *     one we added in the previous migration. Either is fine, but
 *     the explicit named drop can blow up if MySQL never created
 *     a separate index under that name. The drop is wrapped in a
 *     soft-fail so a missing index is a no-op, not a deploy
 *     failure.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sources', 'source_group_id')) {
            return;
        }

        // Try the explicit index drop first. If MySQL never created a
        // named index by that name (the FK provided its own implicit
        // one), swallow the resulting "Can't DROP" error and move on.
        try {
            Schema::table('sources', function (Blueprint $table) {
                $table->dropIndex('sources_source_group_id_index');
            });
        } catch (\Throwable $e) {
            // Index either doesn't exist under that name or was
            // already dropped by the FK removal in an earlier
            // partial-deploy. Either way, nothing more to do here.
        }

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
