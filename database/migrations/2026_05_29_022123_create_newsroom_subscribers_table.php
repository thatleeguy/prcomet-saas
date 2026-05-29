<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Email captures from the public newsroom subscribe form.
 *
 * Unique on (company_id, email_hashed) so re-submissions don't pile
 * up rows. The email itself is stored alongside the hash so
 * operators can export the list for digest emails; the hash is what
 * the uniqueness check uses to keep the index size sane.
 *
 * No marketing automation in v0 — this is a capture surface. The
 * digest fan-out is a later piece.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('newsroom_subscribers')) {
            return;
        }

        Schema::create('newsroom_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('email_hashed', 64);
            $table->string('source_ref')->nullable(); // 'newsroom' | 'pasted' etc
            $table->ipAddress('ip')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'email_hashed'], 'newsroom_subs_unique');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsroom_subscribers');
    }
};
