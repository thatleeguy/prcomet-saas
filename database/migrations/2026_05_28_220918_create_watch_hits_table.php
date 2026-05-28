<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Each row is a single occurrence of a watch term in one piece of content.
 *
 * Polymorphic via content_type + content_id so a watch can match against
 * PublicationItems and PressReleases through the same surface. The
 * (watch_id, content_type, content_id) unique tuple stops the scanner
 * from double-recording the same source.
 *
 * `confirmed_by_llm`:
 *   - null  → not LLM-checked (literal-only mode or LLM check pending)
 *   - true  → Claude confirmed the snippet is about the watched thing
 *   - false → Claude rejected it as a false positive
 *
 * Snippets get truncated to ~200 chars on each side of the matched term
 * so the hits list reads at a glance.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Idempotent: a previous deploy may have created the table but failed
        // before recording the migration row (Forge's batch can drop a step
        // mid-flight). Re-running should pick up the missing migration row
        // without trying to recreate an existing table.
        if (Schema::hasTable('watch_hits')) {
            return;
        }

        Schema::create('watch_hits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watch_id')->constrained()->cascadeOnDelete();
            $table->string('content_type');
            $table->unsignedBigInteger('content_id');
            $table->string('matched_term');
            $table->text('context_snippet')->nullable();
            $table->boolean('confirmed_by_llm')->nullable();
            $table->text('llm_reasoning')->nullable();
            $table->timestamp('matched_at');
            $table->timestamps();

            $table->unique(['watch_id', 'content_type', 'content_id'], 'watch_hits_unique_source');
            $table->index(['watch_id', 'matched_at']);
            $table->index(['content_type', 'content_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watch_hits');
    }
};
