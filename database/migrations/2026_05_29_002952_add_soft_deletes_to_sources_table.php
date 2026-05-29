<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soft delete on sources.
 *
 * Removing a source from a catalogue is a routine operation, but the
 * matches the source produced still need to exist (deleting the row
 * would cascade out publication items → matches → wins, vapourising
 * the customer's history). SoftDeletes lets the operator click
 * "remove" without that destruction.
 *
 * The team-visible scope (Source::visibleTo) automatically excludes
 * trashed rows from new ingestion, but rendered matches still resolve
 * via the publication_item → source FK because Eloquent's default
 * query scope respects deleted_at only on the source itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sources', 'deleted_at')) {
            return;
        }

        Schema::table('sources', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
