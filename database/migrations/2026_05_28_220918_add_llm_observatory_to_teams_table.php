<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Account-level upgrade flag for Observatory.
 *
 * Default behaviour for every team is literal-only matching. Setting this
 * flag unlocks the "Literal + LLM confirmation" mode on watches, which
 * has a per-hit cost so it's gated rather than always-on.
 *
 * Toggle lives on Team (not User) because billing is team-level. Admins
 * flip it from the Filament panel when an account is upgraded.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Idempotent — same Forge half-batch concern as the watches tables.
        if (Schema::hasColumn('teams', 'llm_observatory_enabled')) {
            return;
        }

        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('llm_observatory_enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('llm_observatory_enabled');
        });
    }
};
