<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One source belongs to one group. Modelling as belongsTo (rather than
 * many-to-many) keeps the operator UI simple — a source has a clear
 * owner catalogue, no overlap headaches. If a publication needs to
 * appear in two catalogues later, we'll add a pivot, but the marketing
 * examples ("Mining", "Oil & Gas", "Clean Energy") read as mutually
 * exclusive buckets.
 *
 * Nullable for the migration window — backfilled by SourceGroupSeeder
 * which pins every existing source into a default "Mining Publications"
 * group.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sources', 'source_group_id')) {
            return;
        }

        Schema::table('sources', function (Blueprint $table) {
            $table->foreignId('source_group_id')->nullable()->after('team_id')
                ->constrained('source_groups')->nullOnDelete();
            $table->index('source_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_group_id');
        });
    }
};
