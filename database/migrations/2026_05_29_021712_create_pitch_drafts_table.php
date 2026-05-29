<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Latest AI-generated pitch email per (match, tone) tuple.
 *
 * Unique on (match_id, tone) so re-generating with the same tone
 * overwrites the previous draft. Generating with a different tone
 * spawns a new row, letting the user compare formal vs direct
 * vs casual side-by-side without losing prior attempts.
 *
 * Drafts are never auto-sent — they're a clipboard / mailto
 * starting point. We store them so the user can come back to a
 * draft they liked without burning another LLM call.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pitch_drafts')) {
            return;
        }

        Schema::create('pitch_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->string('tone', 16); // formal | direct | casual
            $table->string('subject', 200);
            $table->text('body');
            $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['match_id', 'tone'], 'pitch_drafts_match_tone_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pitch_drafts');
    }
};
