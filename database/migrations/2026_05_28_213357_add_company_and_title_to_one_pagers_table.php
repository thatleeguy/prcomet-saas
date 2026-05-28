<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Standalone one-pagers — let the user create a page that isn't bound
 * to a specific match (e.g., an evergreen company introduction).
 *
 *  - match_id becomes nullable; an unbound page just stays unlinked.
 *  - title is added so an unbound page has a human label, since it can't
 *    fall back to publication_item->title the way a match-bound one does.
 *
 * company_id was already on the table (one_pagers always belonged to a
 * company), so the scope guard for the workspace is unchanged.
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite needs doctrine/dbal for direct column changes; safer to
        // rebuild the FK ourselves. Drop then re-add as nullable.
        Schema::table('one_pagers', function (Blueprint $table) {
            $table->dropForeign(['match_id']);
        });

        Schema::table('one_pagers', function (Blueprint $table) {
            $table->unsignedBigInteger('match_id')->nullable()->change();
            $table->foreign('match_id')->references('id')->on('matches')->cascadeOnDelete();
            $table->string('title')->nullable()->after('match_id');
        });
    }

    public function down(): void
    {
        Schema::table('one_pagers', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropForeign(['match_id']);
        });

        Schema::table('one_pagers', function (Blueprint $table) {
            $table->unsignedBigInteger('match_id')->nullable(false)->change();
            $table->foreign('match_id')->references('id')->on('matches')->cascadeOnDelete();
        });
    }
};
