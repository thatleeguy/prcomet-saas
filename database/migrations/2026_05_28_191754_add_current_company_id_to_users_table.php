<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track which company the user has "in focus" so that the workspace —
     * matches list, dashboard widget, Library and Branding nav items — is
     * always scoped to a single company. Mirrors Jetstream's current_team_id.
     *
     * nullOnDelete so removing a company doesn't nuke the user row; the
     * model layer falls back to the first company in the current team.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_company_id')
                ->nullable()
                ->after('current_team_id')
                ->constrained('companies')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_company_id');
        });
    }
};
