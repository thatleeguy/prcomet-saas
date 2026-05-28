<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Observatory watches — named alerts the user maintains for a company.
 *
 * A watch is one named "thing to watch for" (a competitor, a project
 * name, a piece of equipment, a jurisdiction). It carries a list of
 * literal `terms` (primary name + aliases) that get matched
 * case-insensitive against ingested content.
 *
 * `mode` is either 'literal' (free) or 'literal_llm' (paid). When
 * literal_llm is selected but the team has not enabled
 * llm_observatory_enabled, the watch falls back to literal at scan
 * time — the column stores intent, not entitlement.
 *
 * hit_count + last_matched_at are denormalised so the index page can
 * sort and surface freshness without an N+1.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('kind')->default('term'); // company | location | product | term
            $table->json('terms'); // ['Newmont', 'Newmont Mining', 'Newmont Goldcorp']
            $table->string('mode')->default('literal'); // literal | literal_llm
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_matched_at')->nullable();
            $table->unsignedInteger('hit_count')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watches');
    }
};
